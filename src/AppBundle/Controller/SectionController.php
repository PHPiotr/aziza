<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Exception\IOException;
use AppBundle\Form\SectionType;
use AppBundle\Form\SectionDeleteType;
use AppBundle\Entity\Section;
use AppBundle\Entity\SectionPhoto;
use Symfony\Component\Filesystem\Filesystem;
use Gedmo\Sluggable\Util as Sluggable;

class SectionController extends Controller
{

    private $sectionPhotosDirectory = 'bundles/app/img/section/';
    private $perPage = 10;

    protected function getListData($page = 1)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Section');
        $sections = $repo->findBy([], ['title' => 'ASC'], $this->perPage, $this->perPage * ($page - 1));
        $countAll = $repo->getCountAll();
        $pagesCount = $countAll > 0 ? ceil($countAll / $this->perPage) : 1;

        if ($page > $pagesCount) {
            $page = $pagesCount;
        }

        return ['sections' => $sections, 'currentPage' => $page, 'pagesCount' => $pagesCount];
    }

    /**
     * @Route("/admin/sekcja/{page}", name="admin_section_list", requirements={"page": "\d+"}, defaults={"page" = 1}))
     * @Template()
     */
    public function adminListAction($page)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);

        return $this->getListData($page);
    }

    /**
     * @Route("/admin/sekcja/edycja/{id}", name="admin_section_edit", requirements={"id": "\d+"}))
     * @Template()
     */
    public function adminEditAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);
        $repo = $this->getDoctrine()->getRepository('AppBundle:Section');
        $section = $repo->findOneById($id);
        if (null === $section) {
            throw $this->createNotFoundException('Nie ma takiego pokoju.');
        }
        $photo = $this->getDoctrine()->getRepository('AppBundle:SectionPhoto')->findOneById($section->getMainPhotoId());
        $mainPhoto = null !== $photo ? $photo->getName() : null;

        $form = $this->createForm(SectionType::class, $section);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return ['mainPhoto' => $mainPhoto, 'sections' => $this->getSections(), 'galleries' => $this->getGalleries(), 'section' => $section, 'form' => $form->createView()];
        }

        if (!$form->isValid()) {
            foreach ($form->getErrors() as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
            return ['mainPhoto' => $mainPhoto, 'sections' => $this->getSections(), 'galleries' => $this->getGalleries(), 'section' => $section, 'form' => $form->createView()];
        }

        try {
            if (true !== $this->addSectionPhotosIfNeeded($request, $section, new Filesystem())) {
                throw new \Exception('Nie powiodło się dodanie pliku');
            }
            $title = $section->getTitle();
            $slug = Sluggable\Urlizer::urlize($title);
            $sameSlugSection = $repo->findOneBy(['slug' => $slug]);

            if (null === $repo->findOneBy(['slug' => $slug])) {
                $section->setSlug(Sluggable\Urlizer::urlize($title));
            } else if ($sameSlugSection->getId() !== $section->getId()) {
                $availableSlug = $this->getAvailableSlugForTitle($title);
                $section->setSlug($availableSlug);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'Pokój został zmodyfikowany');
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
            return ['mainPhoto' => $mainPhoto, 'sections' => $this->getSections(), 'galleries' => $this->getGalleries(), 'section' => $section, 'form' => $form->createView()];
        }

        return $this->redirectToRoute('admin_section_edit', ['id' => $section->getId()]);
    }

    /**
     * @Route("/admin/sekcja/usuwanie/{id}", name="admin_section_delete", requirements={"id": "\d+"}))
     * @Template()
     */
    public function adminDeleteAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);
        $section = $this->getDoctrine()->getRepository('AppBundle:Section')->findOneById($id);
        if (null === $section) {
            throw $this->createNotFoundException('Nie ma takiego pokoju');
        }

        $form = $this->createForm(SectionDeleteType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return ['section' => $section, 'form' => $form->createView()];
        }

        if (!$form->isValid()) {
            foreach ($form->getErrors() as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
            return ['section' => $section, 'form' => $form->createView()];
        }

        $this->deleteSectionPhotosIfNeeded($section);

        $em = $this->getDoctrine()->getManager();
        $em->remove($section);
        $em->flush();
        $this->addFlash('success', 'Pokój został usunięty');

        return $this->redirectToRoute('admin_section_list');
    }

    /**
     * @Route("/admin/sekcja/dodawanie", name="admin_section_add")
     * @Template()
     */
    public function adminAddAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);

        $section = new Section();
        $form = $this->createForm(SectionType::class, $section);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return ['form' => $form->createView()];
        }

        if (!$form->isValid()) {
            foreach ($form->getErrors() as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
            return ['form' => $form->createView()];
        }

        $title = $section->getTitle();
        $slug = Sluggable\Urlizer::urlize($title);
        $repo = $this->getDoctrine()->getRepository('AppBundle:Section');
        if (null === $repo->findOneBy(['slug' => $slug])) {
            $section->setSlug(Sluggable\Urlizer::urlize($title));
        } else {
            $availableSlug = $this->getAvailableSlugForTitle($title);
            $section->setSlug($availableSlug);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($section);

        try {
            if (true !== $this->addSectionPhotosIfNeeded($request, $section, new Filesystem())) {
                throw new \Exception('Nie powiodło się dodawanie pliku');
            }
        } catch (\Exception $e) {
            return ['form' => $form->createView()];
        }
        $em->flush();
        $this->addFlash('success', 'Pokój został dodany');

        return $this->redirectToRoute('admin_section_list');
    }

    /**
     * @Route("/admin/sekcja/fotografie/{id}", name="admin_section_photos", requirements={"id": "\d+"})
     * @Template()
     */
    public function adminListPhotos(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);
        $section = $this->getDoctrine()->getRepository('AppBundle:Section')->findOneById($id);

        $form = $this->createFormBuilder($section)->getForm();

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return ['sections' => $this->getSections(), 'galleries' => $this->getGalleries(), 'section' => $section, 'form' => $form->createView()];
        }

        if (!$form->isValid()) {
            foreach ($form->getErrors() as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
            return ['sections' => $this->getSections(), 'galleries' => $this->getGalleries(), 'section' => $section, 'form' => $form->createView()];
        }

        if (empty($request->request->get('main'))) {
            if (empty($request->request->get('photo'))) {
                $this->addFlash('warning', 'Nie zaznaczono fotografii do usunięcia');
                return ['sections' => $this->getSections(), 'galleries' => $this->getGalleries(), 'section' => $section, 'form' => $form->createView()];
            }
        }

        if (!empty($request->request->get('photo'))) {
            $this->deleteSectionPhotosIfNeeded($section, $request->request->get('photo'));
            $this->addFlash('success', 'Zdjęcia pokoju usunięte');
        }


        $repo = $this->getDoctrine()->getRepository('AppBundle:SectionPhoto');
        $photo = $repo->findOneById($request->request->get('main'));
        $em = $this->getDoctrine()->getManager();
        if (null !== $photo) {
            $section->setMainPhotoId($photo->getId());
            $em->persist($section);
            $em->flush();
            $this->addFlash('success', 'Zdjęcie główne zostało ustawione');
        } else {
            if ($section->getMainPhotoId()) {
                $photo = $repo->findOneById($section->getMainPhotoId());
                if (null == $photo) {
                    $section->setMainPhotoId(null);
                    $em->flush();
                    $this->addFlash('warning', 'Zdjęcie główne zostało usunięte');
                }
            }
        }

        return $this->redirectToRoute('admin_section_photos', ['id' => $section->getId(), 'request' => $request,]);
    }

    protected function addSectionPhotosIfNeeded(Request $request, Section $section, Filesystem $fs)
    {
        $files = $request->files->all();

        if (!isset($files['section']['sectionPhotos'])) {
            return true;
        }
        $sectionPhotos = $files['section']['sectionPhotos'];
        if ($sectionPhotos[0] === null) {
            return true;
        }
        $sectionPhotosDirectory = $this->sectionPhotosDirectory;

        $em = $this->getDoctrine()->getManager();
        $imgAdded = $thumbAdded = [];
        $error = null;

        foreach ($sectionPhotos as $uploadedFile) {
            $sectionPhoto = new SectionPhoto();
            $clientOriginalName = $uploadedFile->getClientOriginalName();
            $extension = pathinfo($clientOriginalName, PATHINFO_EXTENSION);
            $sluggedFilename = sprintf('%s-%s', Sluggable\Urlizer::urlize($section->getTitle()), md5(time() . rand(0, 1000)));
            $sluggedName = strtolower(sprintf('%s.%s', $sluggedFilename, $extension));

            $sectionPhoto->setSection($section);
            $sectionPhoto->setName($sluggedName);
            $em->persist($sectionPhoto);

            $original = $sectionPhotosDirectory . $sluggedName;
            $thumbnailMedium = $sectionPhotosDirectory . 'thumbnails/' . $sluggedName;

            try {
                $uploadedFile->move($sectionPhotosDirectory, $sluggedName);
                $imgAdded[] = $original;
                $fs->copy($sectionPhotosDirectory . $sluggedName, $thumbnailMedium);
                $imgAdded[] = $thumbnailMedium;
                $thumbAdded[] = $thumbnailMedium;
            } catch (\Exception $e) {
                $error = $e->getMessage();
                break;
            }
        }

        $memory_limit = ini_get('memory_limit');
        ini_set('memory_limit', -1);
        $thumbnail = $this->get('app.utils.thumbnail');
        foreach ($thumbAdded as $thumbnailMedium) {
            $thumbnail->create($thumbnailMedium, 'medium_thumb_out');
        }
        ini_set('memory_limit', $memory_limit);

        if (!$error) {
            $this->addFlash('success', 'Zdjęcia dodane do pokoju');
            $em->flush();

            return true;
        }

        $this->addFlash('danger', $error);
        $fs->remove($imgAdded);

        return false;
    }

    protected function deleteSectionPhotosIfNeeded(Section $section, array $selectedPhotos = [])
    {
        $photos = $section->getSectionPhotos();

        if (!$photos) {
            return;
        }

        $fs = new Filesystem();
        $sectionPhotosDirectory = $this->sectionPhotosDirectory;
        $imgRemove = [];

        $em = $this->getDoctrine()->getManager();
        foreach ($photos as $sectionPhoto) {
            if (!empty($selectedPhotos)) {
                if (!in_array($sectionPhoto->getId(), $selectedPhotos)) {
                    continue;
                }
            }
            $name = $sectionPhoto->getName();
            $em->remove($sectionPhoto);
            $original = $sectionPhotosDirectory . $name;
            $thumbnailMedium = $sectionPhotosDirectory . 'thumbnails/' . $name;
            $imgRemove[] = $original;
            $imgRemove[] = $thumbnailMedium;
        }

        try {
            $fs->remove($imgRemove);
            $em->flush();
        } catch (IOException $e) {
            $this->addFlash('warning', $e->getMessage());
        }
    }

    protected function getAvailableSlugForTitle($title, $index = 0)
    {
        $suffix = $index > 0 ? sprintf('-%d', $index) : null;
        $slug = Sluggable\Urlizer::urlize($title) . $suffix;
        $repo = $this->getDoctrine()->getRepository('AppBundle:Section');
        if (null === $repo->findOneBy(['slug' => $slug])) {
            return $slug;
        }
        return $this->getAvailableSlugForTitle($title, $index + 1);
    }

    protected function getSections()
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Section');
        $sections = $repo->findBy([], ['title' => 'ASC']);

        return $sections;
    }

    protected function getGalleries()
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Gallery');
        $sections = $repo->findBy([], ['title' => 'ASC']);

        return $sections;
    }

}
