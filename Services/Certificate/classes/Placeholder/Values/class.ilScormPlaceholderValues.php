<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilScormPlaceholderValues implements ilCertificatePlaceholderValues
{
	/**
	 * @var ilLanguage|null
	 */
	private $language;

	/**
	 * @var ilDefaultPlaceholderValues|null
	 */
	private $defaultPlaceHolderValuesObject;

	/**
	 * @var ilCertificateDateHelper|null
	 */
	private $dateHelper;

	/**
	 * @var ilCertificateObjectHelper|null
	 */
	private $objectHelper;

	/**
	 * @var ilCertificateUtilHelper|null
	 */
	private $utilHelper;

	/**
	 * @var ilCertificateObjectLPHelper|null
	 */
	private $objectLPHelper;

	/**
	 * @var ilCertificateLPStatusHelper|null
	 */
	private $lpStatusHelper;

	/**
	 * @param ilDefaultPlaceholderValues|null $defaultPlaceholderValues
	 * @param ilLanguage|null $language
	 * @param ilCertificateDateHelper|null $dateHelper
	 * @param ilCertificateObjectHelper|null $objectHelper
	 * @param ilCertificateUtilHelper|null $utilHelper
	 * @param ilCertificateObjectLPHelper|null $objectLPHelper
	 * @param ilCertificateLPStatusHelper|null $lpStatusHelper
	 */
	public function __construct(
		ilDefaultPlaceholderValues $defaultPlaceholderValues = null,
		ilLanguage $language = null,
		ilCertificateDateHelper $dateHelper = null,
		ilCertificateObjectHelper $objectHelper = null,
		ilCertificateUtilHelper $utilHelper = null,
		ilCertificateObjectLPHelper $objectLPHelper = null,
		ilCertificateLPStatusHelper $lpStatusHelper = null
	) {
		if (null === $language) {
			global $DIC;
			$language = $DIC->language();
		}
		$this->language = $language;

		if (null === $defaultPlaceholderValues) {
			$defaultPlaceholderValues = new ilDefaultPlaceholderValues();
		}
		$this->defaultPlaceHolderValuesObject = $defaultPlaceholderValues;

		if (null === $dateHelper) {
			$dateHelper = new ilCertificateDateHelper();
		}
		$this->dateHelper = $dateHelper;

		if (null === $objectHelper) {
			$objectHelper = new ilCertificateObjectHelper();
		}
		$this->objectHelper = $objectHelper;

		if (null === $utilHelper) {
			$utilHelper = new ilCertificateUtilHelper();
		}
		$this->utilHelper = $utilHelper;

		if (null === $objectLPHelper) {
			$objectLPHelper = new ilCertificateObjectLPHelper();
		}
		$this->objectLPHelper = $objectLPHelper;

		if (null === $lpStatusHelper) {
			$lpStatusHelper = new ilCertificateLPStatusHelper();
		}
		$this->lpStatusHelper = $lpStatusHelper;
	}

	/**
	 * This method MUST return an array that contains the
	 * actual data for the given user of the given object.
	 *
	 * ilInvalidCertificateException MUST be thrown if the
	 * data could not be determined or the user did NOT
	 * achieve the certificate.
	 *
	 * @param $userId
	 * @param $objId
	 * @throws ilInvalidCertificateException
	 * @return mixed - [PLACEHOLDER] => 'actual value'
	 * @throws ilException
	 */
	public function getPlaceholderValues(int $userId, int $objId) : array
	{
		$this->language->loadLanguageModule('certificate');

		$object = $this->objectHelper->getInstanceByObjId($objId);
		$points = $object->getPointsInPercent();
		$txtPoints = number_format($points, 1, $this->language->txt('lang_sep_decimal'), $this->language->txt('lang_sep_thousand')) . ' %';
		if (is_null($points)) {
			$txtPoints = $this->language->txt('certificate_points_notavailable');
		}

		$max_points = $object->getMaxPoints();
		$txtMaxPoints = $max_points;
		if (is_null($max_points)) {
			$txtMaxPoints = $this->language->txt('certificate_points_notavailable');
		} else if($max_points != floor($max_points)) {
			$txtMaxPoints = number_format($max_points, 1, $this->language->txt('lang_sep_decimal'), $this->language->txt('lang_sep_thousand'));
		}

		$completionDate = $this->lpStatusHelper->lookupStatusChanged($objId, $userId);

		$placeHolders = $this->defaultPlaceHolderValuesObject->getPlaceholderValues($userId, $objId);

		$placeHolders['SCORM_TITLE']        = $this->utilHelper->prepareFormOutput($object->getTitle());
		$placeHolders['SCORM_POINTS']       = $txtPoints;
		$placeHolders['SCORM_POINTS_MAX']   = $txtMaxPoints;
		$placeholders['DATE_COMPLETED']     = $this->dateHelper->formatDate($completionDate);
		$placeholders['DATETIME_COMPLETED'] = $this->dateHelper->formatDate($completionDate);

		$olp = $this->objectLPHelper->getInstance($object->getId());
		$collection = $olp->getCollectionInstance();

		if($collection) {
			$counter = 0;
			foreach($collection->getPossibleItems() as $item_id => $sahs_item) {
				if($collection->isAssignedEntry($item_id)) {
					$placeHolders['SCO_T_' . $counter] = $sahs_item['title'];
					$a_scores = $collection->getScoresForUserAndCP_Node_Id($item_id, $userId);

					$placeHolders['SCO_P_' . $counter] = $this->language->txt('certificate_points_notavailable');
					if ($a_scores['raw'] !== null) {
						$placeHolders['SCO_P_' . $counter] = number_format(
							$a_scores['raw'],
							1,
							$this->language->txt('lang_sep_decimal'),
							$this->language->txt('lang_sep_thousand')
						);
					}

					$placeHolders['SCO_PM_' . $counter] = $this->language->txt('certificate_points_notavailable');
					if ($a_scores['max'] !== null) {
						$placeHolders['SCO_PM_' . $counter] = number_format(
							$a_scores['max'],
							1,
							$this->language->txt('lang_sep_decimal'),
							$this->language->txt('lang_sep_thousand')
						);
					}

					$placeHolders['SCO_PP_' . $counter] = $this->language->txt('certificate_points_notavailable');
					if ($a_scores['scaled'] !== null) {
						$placeHolders['SCO_PP_' . $counter] = number_format(
							($a_scores['scaled'] * 100),
							1,
							$this->language->txt('lang_sep_decimal'),
							$this->language->txt('lang_sep_thousand')
						);

						$placeHolders['SCO_PP_' . $counter] .= ' %';
					}

					$counter++;
				}
			}
		}

		return $placeHolders;
	}

	/**
	 * This method is different then the 'getPlaceholderValues' method, this
	 * method is used to create a placeholder value array containing dummy values
	 * that is used to create a preview certificate.
	 *
	 * @return array
	 */
	public function getPlaceholderValuesForPreview() : array
	{
		return $this->defaultPlaceHolderValuesObject->getPlaceholderValuesForPreview();
	}
}
