<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Data\Factory;
use ILIAS\Validation\Constraint;
use ILIAS\Validation\Constraints\Custom;

/**
 * Class ilTermsOfServiceDocumentCriterionAssignmentConstraint
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentCriterionAssignmentConstraint extends Custom implements Constraint
{
	/** @var \ilTermsOfServiceDocument */
	protected $document;

	/**
	 * ilTermsOfServiceDocumentCriterionAssignmentConstraint constructor.
	 * @param ilTermsOfServiceDocument $document
	 * @param Factory $dataFactory
	 */
	public function __construct(
		\ilTermsOfServiceDocument $document,
		Factory $dataFactory
	) {
		$this->document = $document;

		parent::__construct(
			function (\ilTermsOfServiceDocumentCriterionAssignment $value) {
				$criteria = $this->document->getCriteria();

				return 0 === count(array_filter($criteria, function(\ilTermsOfServiceDocumentCriterionAssignment $assignment) use ($value) {
					$criterionIdCurrent = $assignment->getCriterionId(); 
					$criterionIdNew = $value->getCriterionId();

					$valueCurrent = $assignment->getCriterionValue();
					$valueNew = $value->getCriterionValue();

					$idCurrent = $assignment->getId();
					$idNew = $value->getId();

					$equalCriteria = (
						$idCurrent != $idNew &&
						$criterionIdCurrent == $criterionIdNew &&
						$valueCurrent == $valueNew 
					);

					return $equalCriteria;
				}));
			},
			function ($value) {
				return "The passed assignment must be unique for the document!";
			},
			$dataFactory
		);
	}
}