<?php
namespace AppBundle\Utils;

use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

class Thumbnail
{
	private $dataManager;
	private $filterManager;

	public function setDataManager(DataManager $dataManager)
	{
		$this->dataManager = $dataManager;
	}

	public function getDataManager()
	{
		return $this->dataManager;
	}

	public function setFilterManager(FilterManager $filterManager)
	{
		$this->filterManager = $filterManager;
	}

	public function getFilterManager()
	{
		return $this->filterManager;
	}

	public function __construct(DataManager $dataManager, FilterManager $filterManager)
	{
		$this->setDataManager($dataManager);
		$this->setFilterManager($filterManager);
	}

	/**
	 * Resize image.
	 *
	 * @param string $image Path to image which should be thumbnailed.
	 * @param string $filter Filter to use for making thumbnail
	 */
	public function create($image, $filter = 'my_thumb_out')
	{
		/**
		 * Liip\ImagineBundle\Model\Binary $binary
		 */
		$binary = $this->getDataManager()->find($filter, $image);
		$response = $this->getFilterManager()->applyFilter($binary, $filter);
		$thumbnail = $response->getContent();
		$f = fopen($image, 'w');
		fwrite($f, $thumbnail);
		fclose($f);
	}
}