<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Exception\IOException;
use AppBundle\Form\GalleryType;
use AppBundle\Form\GalleryDeleteType;
use AppBundle\Entity\Gallery;
use AppBundle\Entity\GalleryPhoto;
use Symfony\Component\Filesystem\Filesystem;
use Gedmo\Sluggable\Util as Sluggable;

class GalleryController extends Controller
{

    private $galleryPhotosDirectory = 'bundles/app/img/gallery/';
    private $perPage = 10;

    protected function getListData($page = 1)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Gallery');
        $galleries = $repo->findBy([], ['title' => 'ASC'], $this->perPage, $this->perPage * ($page - 1));
        $countAll = $repo->getCountAll();
        $pagesCount = $countAll > 0 ? ceil($countAll / $this->perPage) : 1;

        if ($page > $pagesCount) {
            $page = $pagesCount;
        }

        return ['galleries' => $galleries, 'currentPage' => $page, 'pagesCount' => $pagesCount];
    }

    /**
     * @Route("/admin/galeria/{page}", name="admin_gallery_list", requirements={"page": "\d+"}, defaults={"page" = 1}))
     * @Template()
     */
    public function adminListAction($page)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);

        return $this->getListData($page);
    }

    /**
     * @Route("/admin/galeria/edycja/{id}", name="admin_gallery_edit", requirements={"id": "\d+"}))
     * @Template()
     */
    public function adminEditAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);
        $repo = $this->getDoctrine()->getRepository('AppBundle:Gallery');
        $gallery = $repo->findOneById($id);
        if (null === $gallery) {
            throw $this->createNotFoundException('Nie ma takiego pokoju.');
        }
        $photo = $this->getDoctrine()->getRepository('AppBundle:GalleryPhoto')->findOneById($gallery->getMainPhotoId());
        $mainPhoto = null !== $photo ? $photo->getName() : null;

        $form = $this->createForm(GalleryType::class, $gallery);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return ['mainPhoto' => $mainPhoto, 'galleries' => $this->getGalleries(), 'sections' => $this->getSections(), 'gallery' => $gallery, 'form' => $form->createView()];
        }

        if (!$form->isValid()) {
            foreach ($form->getErrors() as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
            return ['mainPhoto' => $mainPhoto, 'galleries' => $this->getGalleries(), 'sections' => $this->getSections(), 'gallery' => $gallery, 'form' => $form->createView()];
        }

        try {
            if (true !== $this->addGalleryPhotosIfNeeded($request, $gallery, new Filesystem())) {
                throw new \Exception('Nie powiodło się dodanie pliku');
            }
            $title = $gallery->getTitle();
            $slug = Sluggable\Urlizer::urlize($title);
            $sameSlugGallery = $repo->findOneBy(['slug' => $slug]);

            if (null === $repo->findOneBy(['slug' => $slug])) {
                $gallery->setSlug(Sluggable\Urlizer::urlize($title));
            } else if ($sameSlugGallery->getId() !== $gallery->getId()) {
                $availableSlug = $this->getAvailableSlugForTitle($title);
                $gallery->setSlug($availableSlug);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'Pokój został zmodyfikowany');
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
            return ['mainPhoto' => $mainPhoto, 'galleries' => $this->getGalleries(), 'sections' => $this->getSections(), 'gallery' => $gallery, 'form' => $form->createView()];
        }

        return $this->redirectToRoute('admin_gallery_edit', ['id' => $gallery->getId()]);
    }

    /**
     * @Route("/admin/galeria/usuwanie/{id}", name="admin_gallery_delete", requirements={"id": "\d+"}))
     * @Template()
     */
    public function adminDeleteAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);
        $gallery = $this->getDoctrine()->getRepository('AppBundle:Gallery')->findOneById($id);
        if (null === $gallery) {
            throw $this->createNotFoundException('Nie ma takiego pokoju');
        }

        $form = $this->createForm(GalleryDeleteType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return ['gallery' => $gallery, 'form' => $form->createView()];
        }

        if (!$form->isValid()) {
            foreach ($form->getErrors() as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
            return ['gallery' => $gallery, 'form' => $form->createView()];
        }

        $this->deleteGalleryPhotosIfNeeded($gallery);

        $em = $this->getDoctrine()->getManager();
        $em->remove($gallery);
        $em->flush();
        $this->addFlash('success', 'Pokój został usunięty');

        return $this->redirectToRoute('admin_gallery_list');
    }

    /**
     * @Route("/admin/galeria/dodawanie", name="admin_gallery_add")
     * @Template()
     */
    public function adminAddAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);

        $gallery = new Gallery();
        $form = $this->createForm(GalleryType::class, $gallery);
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

        $title = $gallery->getTitle();
        $slug = Sluggable\Urlizer::urlize($title);
        $repo = $this->getDoctrine()->getRepository('AppBundle:Gallery');
        if (null === $repo->findOneBy(['slug' => $slug])) {
            $gallery->setSlug(Sluggable\Urlizer::urlize($title));
        } else {
            $availableSlug = $this->getAvailableSlugForTitle($title);
            $gallery->setSlug($availableSlug);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($gallery);

        try {
            if (true !== $this->addGalleryPhotosIfNeeded($request, $gallery, new Filesystem())) {
                throw new \Exception('Nie powiodło się dodawanie pliku');
            }
        } catch (\Exception $e) {
            return ['form' => $form->createView()];
        }
        $em->flush();
        $this->addFlash('success', 'Pokój został dodany');

        return $this->redirectToRoute('admin_gallery_list');
    }

    /**
     * @Route("/admin/galeria/fotografie/{id}", name="admin_gallery_photos", requirements={"id": "\d+"})
     * @Template()
     */
    public function adminListPhotos(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);
        $gallery = $this->getDoctrine()->getRepository('AppBundle:Gallery')->findOneById($id);

        $form = $this->createFormBuilder($gallery)->getForm();

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return ['galleries' => $this->getGalleries(), 'sections' => $this->getSections(), 'gallery' => $gallery, 'form' => $form->createView()];
        }

        if (!$form->isValid()) {
            foreach ($form->getErrors() as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
            return ['galleries' => $this->getGalleries(), 'sections' => $this->getSections(), 'gallery' => $gallery, 'form' => $form->createView()];
        }

        if (empty($request->request->get('main'))) {
            if (empty($request->request->get('photo'))) {
                $this->addFlash('warning', 'Nie zaznaczono fotografii do usunięcia');
                return ['galleries' => $this->getGalleries(), 'sections' => $this->getSections(), 'gallery' => $gallery, 'form' => $form->createView()];
            }
        }

        if (!empty($request->request->get('photo'))) {
            $this->deleteGalleryPhotosIfNeeded($gallery, $request->request->get('photo'));
            $this->addFlash('success', 'Zdjęcia pokoju usunięte');
        }


        $repo = $this->getDoctrine()->getRepository('AppBundle:GalleryPhoto');
        $photo = $repo->findOneById($request->request->get('main'));
        $em = $this->getDoctrine()->getManager();
        if (null !== $photo) {
            $gallery->setMainPhotoId($photo->getId());
            $em->persist($gallery);
            $em->flush();
            $this->addFlash('success', 'Zdjęcie główne zostało ustawione');
        } else {
            if ($gallery->getMainPhotoId()) {
                $photo = $repo->findOneById($gallery->getMainPhotoId());
                if (null == $photo) {
                    $gallery->setMainPhotoId(null);
                    $em->flush();
                    $this->addFlash('warning', 'Zdjęcie główne zostało usunięte');
                }
            }
        }

        return $this->redirectToRoute('admin_gallery_photos', ['id' => $gallery->getId(), 'request' => $request,]);
    }

    protected function addGalleryPhotosIfNeeded(Request $request, Gallery $gallery, Filesystem $fs)
    {
        $files = $request->files->all();

        if (!isset($files['gallery']['galleryPhotos'])) {
            return true;
        }
        $galleryPhotos = $files['gallery']['galleryPhotos'];
        if ($galleryPhotos[0] === null) {
            return true;
        }
        $galleryPhotosDirectory = $this->galleryPhotosDirectory;

        $em = $this->getDoctrine()->getManager();
        $imgAdded = $thumbAdded = [];
        $error = null;

        foreach ($galleryPhotos as $uploadedFile) {
            $galleryPhoto = new GalleryPhoto();
            $clientOriginalName = $uploadedFile->getClientOriginalName();
            $extension = pathinfo($clientOriginalName, PATHINFO_EXTENSION);
            $sluggedFilename = sprintf('%s-%s', Sluggable\Urlizer::urlize($gallery->getTitle()), md5(time() . rand(0, 1000)));
            $sluggedName = strtolower(sprintf('%s.%s', $sluggedFilename, $extension));

            $galleryPhoto->setGallery($gallery);
            $galleryPhoto->setName($sluggedName);
            $em->persist($galleryPhoto);

            $original = $galleryPhotosDirectory . $sluggedName;
            $thumbnailMedium = $galleryPhotosDirectory . 'thumbnails/' . $sluggedName;

            try {
                $uploadedFile->move($galleryPhotosDirectory, $sluggedName);
                $imgAdded[] = $original;
                $fs->copy($galleryPhotosDirectory . $sluggedName, $thumbnailMedium);
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

    protected function deleteGalleryPhotosIfNeeded(Gallery $gallery, array $selectedPhotos = [])
    {
        $photos = $gallery->getGalleryPhotos();

        if (!$photos) {
            return;
        }

        $fs = new Filesystem();
        $galleryPhotosDirectory = $this->galleryPhotosDirectory;
        $imgRemove = [];

        $em = $this->getDoctrine()->getManager();
        foreach ($photos as $galleryPhoto) {
            if (!empty($selectedPhotos)) {
                if (!in_array($galleryPhoto->getId(), $selectedPhotos)) {
                    continue;
                }
            }
            $name = $galleryPhoto->getName();
            $em->remove($galleryPhoto);
            $original = $galleryPhotosDirectory . $name;
            $thumbnailMedium = $galleryPhotosDirectory . 'thumbnails/' . $name;
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
        $repo = $this->getDoctrine()->getRepository('AppBundle:Gallery');
        if (null === $repo->findOneBy(['slug' => $slug])) {
            return $slug;
        }
        return $this->getAvailableSlugForTitle($title, $index + 1);
    }

    /**
     * @Route("/galeria/{slug}", name="gallery")
     * @Template()
     */
    public function indexAction(Request $request, $slug = null)
    {
        $base = new \AppBundle\Utils\Base($this->getDoctrine());
        if (!$slug) {
            return ['backgroundFileName' => $base->getFileFor('tlo')] + $this->getListData();
        }

        $gallery = $this->getDoctrine()->getRepository('AppBundle:Gallery')->findOneBy(['slug' => $slug]);
        if (null === $gallery) {
            throw $this->createNotFoundException('This car is not in stock.');
        }


        return $this->render('AppBundle:Gallery:gallery.html.twig', ['backgroundFileName' => $base->getFileFor('tlo'), 'gallery' => $gallery]);
    }

    protected function getGalleries()
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Gallery');
        $galleries = $repo->findBy([], ['title' => 'ASC']);

        return $galleries;
    }

    protected function getSections()
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Section');
        $sections = $repo->findBy([], ['title' => 'ASC']);

        return $sections;
    }

}
