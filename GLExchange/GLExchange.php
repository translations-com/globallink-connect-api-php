<?php
require_once 'pd_ws/client/ProjectService_4180.php';
require_once 'pd_ws/client/SubmissionService_4180.php';
require_once 'pd_ws/client/TargetService_4180.php';
require_once 'pd_ws/client/DocumentService_4180.php';
require_once 'pd_ws/client/UserProfileService_4180.php';
require_once 'pd_ws/client/WorkflowService_4180.php';
require_once 'model/Project.inc.php';
require_once 'model/Submission.inc.php';
require_once 'model/Target.inc.php';
define ( 'GL_WSDL_PATH', __DIR__ . '/pd_ws/wsdl/' );
define ( 'USER_PROFILE_SERVICE_WSDL', GL_WSDL_PATH . 'UserProfileService_4180.wsdl' );
define ( 'SUBMISSION_SERVICE_WSDL', GL_WSDL_PATH . 'SubmissionService_4180.wsdl' );
define ( 'WORKFLOW_SERVICE_WSDL', GL_WSDL_PATH . 'WorkflowService_4180.wsdl' );
define ( 'DOCUMENT_SERVICE_WSDL', GL_WSDL_PATH . 'DocumentService_4180.wsdl' );
define ( 'PROJECT_SERVICE_WSDL', GL_WSDL_PATH . 'ProjectService_4180.wsdl' );
define ( 'TARGET_SERVICE_WSDL', GL_WSDL_PATH . 'TargetService_4180.wsdl' );
define ( 'DELAY_TIME', 2 );
class GLExchange {
	private $pdConfig; // PDConfig
	private $submission; // Submission
	private $projectService;
	private $userProfileService;
	private $targetService;
	private $documentService;
	private $submissionService;
	private $workflowService;
    private $adaptorName;
    private $adaptorVersion;
    private $clientVersion;
    private $technologyProduct = "GL_PD";

	/**
	 *
	 * Initialize Project Director connector
	 *
	 * @param
	 *        	$connectionConfig
	 *        	PDConfig - Project Director Connection Configuration
	 */
	function __construct(\PDConfig $connectionConfig) {
		$this->_setConnectionConfig ( $connectionConfig );
	}

	/**
	 *
	 * Set/update Project Director connection configuration
	 *
	 * @param
	 *        	$connectionConfig
	 *        	PDConfig - Project Director Connection Configuration
	 * @throws Exception
	 */
	private function _setConnectionConfig(\PDConfig $connectionConfig) {
		$this->pdConfig = $connectionConfig;
		if (! isset ( $this->pdConfig->url ))
			throw new Exception ( "Invalid URL" );
		if (! isset ( $this->pdConfig->username ))
			throw new Exception ( "Invalid username" );
		if (! isset ( $this->pdConfig->password ))
			throw new Exception ( "Invalid Password" );
		if (! isset ( $this->pdConfig->userAgent ))
			throw new Exception ( "Invalid User Agent" );
		$proxyConfig = array ();
		if (isset ( $connectionConfig->proxyConfig )) {
			$proxyConfig = array (
					'proxy_host' => $connectionConfig->proxyConfig->proxyHost,
					'proxy_port' => $connectionConfig->proxyConfig->proxyPort,
					'proxy_login' => $connectionConfig->proxyConfig->proxyUser,
					'proxy_password' => $connectionConfig->proxyConfig->proxyPassword
			);
		}

		$soapConfig = array("stream_context" => stream_context_create(
                [
                    'ssl' => [
                        'verify_peer'      => false,
                        'verify_peer_name' => false,
                    ]
                ]
            ));

		$security = '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">' . '<wsse:UsernameToken xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" wsu:Id="UsernameToken-1">' . '<wsse:Username>' . $this->pdConfig->username . '</wsse:Username>' . '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' . $this->pdConfig->password . '</wsse:Password>' . '</wsse:UsernameToken></wsse:Security>';
		$userAgent = '<commons:userAgent xmlns:commons="http://commons.ws.projectdirector.gs4tr.org">' . $this->pdConfig->userAgent . '</commons:userAgent>';

		$header = array ();
		$header [] = new SoapHeader ( "Security", 'Security', new SoapVar ( $security, XSD_ANYXML ), true );
		$header [] = new SoapHeader ( "userAgent", 'userAgent', new SoapVar ( $userAgent, XSD_ANYXML ), true );

		$this->projectService = new ProjectService_4180 ( PROJECT_SERVICE_WSDL, array_merge ( array (
				'location' => $this->pdConfig->url . '/services/ProjectService_4180',
		), $soapConfig, $proxyConfig ), $header );
		$this->submissionService = new SubmissionService_4180 ( SUBMISSION_SERVICE_WSDL, array_merge ( array (
				'location' => $this->pdConfig->url . '/services/SubmissionService_4180',
		), $soapConfig, $proxyConfig ), $header );
		$this->workflowService = new WorkflowService_4180 ( WORKFLOW_SERVICE_WSDL, array_merge ( array (
				'location' => $this->pdConfig->url . '/services/WorkflowService_4180'
		), $soapConfig, $proxyConfig ), $header );
		$this->targetService = new TargetService_4180 ( TARGET_SERVICE_WSDL, array_merge ( array (
				'location' => $this->pdConfig->url . '/services/TargetService_4180'
		), $soapConfig, $proxyConfig ), $header );
		$this->documentService = new DocumentService_4180 ( DOCUMENT_SERVICE_WSDL, array_merge ( array (
				'location' => $this->pdConfig->url . '/services/DocumentService_4180'
		), $soapConfig, $proxyConfig ), $header );
		$this->userProfileService = new UserProfileService_4180 ( USER_PROFILE_SERVICE_WSDL, array_merge ( array (
				'location' => $this->pdConfig->url . '/services/UserProfileService_4180'
		), $soapConfig, $proxyConfig ), $header );

		try {
			$getUserProjectsRequest = new getUserProjects ();
			$getUserProjectsRequest->isSubProjectIncluded = TRUE;
			$projects = $this->projectService->getUserProjects ( $getUserProjectsRequest )->return;
		} catch ( Exception $ex ) {
			throw new Exception ( "Invalid config");
		}
	}
	private function _convertTargetsToInternal($targets) {
		$targets_arr = array ();
		if(isset($targets)){
			$pdtargets = is_array($targets)?$targets : array($targets);
			foreach ( $pdtargets as $target ) {
				$pdtarget = new PDTarget ( $target );
				$targets_arr [] = $pdtarget;
			}
		}
		return $targets_arr;
	}
	private function _createSubmissionInfo() {
		$submissionInfo = new SubmissionInfo ();
		$submissionInfo->projectTicket = $this->submission->project->ticket;
		$submissionInfo->name = $this->submission->name;
		if (isset ( $this->submission->pmNotes )) {
			$submissionInfo->internalNotes = $this->submission->pmNotes;
		}

		if (isset ( $this->submission->dueDate )) {
			$dateRequested = new Date();
			$dateRequested->date = $this->submission->dueDate;
			$dateRequested->critical = false;
			$submissionInfo->dateRequested = $dateRequested;
		}

		if (isset ( $this->submission->metadata )) {
			$i = 0;
			$metadatas = array ();
			foreach ( $this->submission->metadata as $k => $v ) {
				$metadata = new Metadata ();
				$metadata->key = substr($k, 0, 255);
				$metadata->value = substr($v, 0, 1024);
				$metadatas [$i ++] = $metadata;
			}
			$submissionInfo->metadata = $metadatas;
		}

		if (isset ( $this->submission->submitter )) {
			$submissionInfo->submitters = array (
					$this->submission->submitter
			);
		}

		if (isset ( $this->submission->isUrgent ) && $this->submission->isUrgent == TRUE) {
			$priority = new Priority ();
			$priority->value = 2;
			$submissionInfo->priority = $priority;
		}

		if (isset ( $this->submission->customAttributes )) {
			$attributes = array ();
			foreach ( $this->submission->customAttributes as $k => $v ) {
				$submissionCustomField = new SubmissionCustomFields ();
				$submissionCustomField->fieldName = $k;
				$submissionCustomField->fieldValue = $v;
				$attributes [] = $submissionCustomField;
			}
			$submissionInfo->submissionCustomFields = $attributes;
		}

		if (isset ( $this->submission->workflow ) && $this->submission->workflow->ticket != "") {
			$submissionInfo->workflowDefinitionTicket = $this->submission->workflow->ticket;
		}
		$submissionInfo->technologyProduct = "GL_PD";
		if(isset($this->adaptorName)){
		    $submissionInfo->adaptorName = $this->adaptorName;
        } else{
		    $submissionInfo->adaptorName = 'Unknown';
        }
        if(isset($this->adaptorVersion)){
            $submissionInfo->adaptorVersion = $this->adaptorVersion;
        } else{
            $submissionInfo->adaptorVersion = 'Unknown';
        }
        if(isset($this->clientVersion)){
            $submissionInfo->clientVersion = $this->clientVersion;
        } else{
            $submissionInfo->clientVersion = 'Unknown';
        }
		return $submissionInfo;
	}

	private function _createWorkflowRequest($submissionWorkflowInfo){
		$workflowRequest = new WorkflowRequest ();
		$workflowRequest->submissionTicket = $submissionWorkflowInfo->submissionTicket;
		$workflowRequest->phaseName = $submissionWorkflowInfo->phaseName;
		$workflowRequest->batchWorkflowInfos = $submissionWorkflowInfo->batchWorkflowInfos;
		$workflowRequest->languageWorkflowInfos = $submissionWorkflowInfo->languageWorkflowInfos;
		$workflowRequest->targetWorkflowInfos = $submissionWorkflowInfo->targetWorkflowInfos;
		return $workflowRequest;
	}

	private function _validateDocument($document) {
		if (! isset ( $document ) || ! isset ( $document->data ) || strlen ( $document->data ) <= 0) {
			throw new Exception ( "Document is empty" );
		}

		if (! isset ( $document->name )) {
			throw new Exception ( "Document name not set" );
		}

		$project = $this->submission->project;

		if(strcasecmp($document->fileformat, "Non-Parsable") != 0 ){
			$isFileFormatCorrect = false;
			foreach ( $project->fileFormats as $fileFormat ) {
				if ($fileFormat == $document->fileformat) {
					$isFileFormatCorrect = true;
					break;
				}
			}
			if (! $isFileFormatCorrect) {
				throw new Exception ( "Specified file format " . $document->fileformat . " doesn`t exist in specified project" );
			}
		}


		if (! isset ( $document->sourceLanguage ) || strlen ( $document->sourceLanguage ) <= 1) {
			throw new Exception ( "Source language not set" );
		}

		if (! isset ( $document->targetLanguages ) || count ( $document->targetLanguages ) <= 0) {
			throw new Exception ( "Target languages are not set" );
		}

		foreach ( $document->targetLanguages as $language ) {
			$isTargetLanguageFound = false;
			foreach ( $project->languageDirections as $languageDirection ) {
				if ($languageDirection->sourceLanguage == $document->sourceLanguage && $languageDirection->targetLanguage == $language) {
					$isTargetLanguageFound = true;
					break;
				}
			}
			if (! $isTargetLanguageFound) {
				throw new Exception ( "Project is not configured for language direction " . $document->sourceLanguage . "->" . $language );
			}
		}
	}
	private function _validateSubmission($submission) {
		if (! isset ( $submission )) {
			throw new Exception ( "Please initialize submission first." );
		}
		if (! isset ( $submission->project )) {
			throw new Exception ( "Please set submission project" );
		}
		if (! isset ( $submission->name )) {
			throw new Exception ( "Please set submission name" );
		}

		if (isset ( $submission->workflow )) {
			$isSet = false;
			foreach ( $submission->project->workflows as $workflow ) {
				if ($workflow->ticket == $submission->workflow->ticket) {
					$isSet = true;
					break;
				}
			}
			if (! $isSet) {
				throw new Exception ( "Invalid submission workflow " + $submission->workflow->name );
			}
		}
		if (isset ( $submission->dueDate )) {
			$today = date ( "m.d.y" );
			if (strtotime ( $today )*1000 > $submission->dueDate) {
				throw new Exception ( "Submission due date should be greater than current date" );
			}
		}

		if (isset ( $submission->submitter )) {
			if(!$this->isSubmitterValid($submission->project->shortcode, $submission->submitter)){
				throw new Exception ( "Specified submitter '" . $submission->submitter . "' doesn`t exist" );
			}
		}

		if (isset ( $submission->project->customAttributes )) {
			foreach ( $submission->project->customAttributes as $projectCustomAttribute ) {
				if ($projectCustomAttribute->mandatory) {
					$isSet = false;
					if (isset ( $submission->customAttributes )) {
						foreach ( $submission->customAttributes as $name => $value ) {
							if ($name == $projectCustomAttribute->name) {
								$isSet = true;
								break;
							}
						}
					}
					if (! $isSet) {
						throw new Exception ( "Mandatory custom field '" . $projectCustomAttribute->name . "' is not set" );
					}
				}
			}
			if (isset ( $submission->customAttributes )) {
				foreach ( $submission->customAttributes as $name => $value ) {
					$isExists = false;
					foreach ( $submission->project->customAttributes as $projectCustomAttribute ) {
						if ($name == $projectCustomAttribute->name) {
							$isExists = true;
							if($projectCustomAttribute->type == "COMBO" && isset($projectCustomAttribute->values)){
								$comboValueCorrect = false;
								$va = explode(",", $projectCustomAttribute->values);
								foreach ( $va as $option ) {
									if($option == $value){
										$comboValueCorrect = true;
										break;
									}
								}
								if(! $comboValueCorrect){
								    throw new Exception("Value '".$value."' for custom field '" . $name . "' is not allowed. Allowed values:".$projectCustomAttribute->values);
								}
							}
						}
					}
					if(! $isExists){
						throw new Exception("Custom field '" . $name . "' is not allowed in project");
				    }
				}
			}
		} else {
			if (isset ( $submission->customAttributes )) {
				throw new Exception("Project doesn't have custom attributes");
		    }
		}
	}

	function downloadTranslationKit($submissionWorkflowInfo){
		$repositoryItems = array ();
		$workflowRequestTickets = array ();
		$workflowRequest = $this->_createWorkflowRequest($submissionWorkflowInfo);

		$downloadWorkflowRequestTicket = $this->workflowService->download($workflowRequest)->return;
		array_push($workflowRequestTickets, $downloadWorkflowRequestTicket);

		// Ping server for checking if download has finished
		while(count($workflowRequestTickets) > 0 ){
			sleep(DELAY_TIME);

			foreach ($workflowRequestTickets as &$workflowRequestTicket) {
				$downloadActionResult = $this->workflowService->checkDownloadAction($workflowRequestTicket)->return;
				if ($downloadActionResult->processingFinished->booleanValue) {
					$repositoryItem = $downloadActionResult->repositoryItem;
					array_push($repositoryItems, $repositoryItem);
					unset($workflowRequestTickets[$workflowRequestTicket]);
				}
			}
		}

		return $repositoryItems;
	}

	/**
	 * Find project by shortcode
	 *
	 * @return PDProject
	 */
	function getProject($projectShortCode) {
		$findProjectByShortCodeRequest = new findProjectByShortCode ();

		$findProjectByShortCodeRequest->projectShortCode = $projectShortCode;

		$project = $this->projectService->findProjectByShortCode ( $findProjectByShortCodeRequest )->return;

		return new PDProject($project);
	}

	/**
	 * Get all user projects
	 *
	 * @return Array of PDProject to which the logged in user has access to
	 */
	function getProjects() {
		$getUserProjectsRequest = new getUserProjects ();
		$getUserProjectsRequest->isSubProjectIncluded = TRUE;
		$projects = $this->projectService->getUserProjects ( $getUserProjectsRequest )->return;

		$i = 0;
		$proj_arr = array ();
		if (is_array ( $projects )) {
			foreach ( $projects as $project ) {
				$pdproject = new PDProject ( $project );
				$proj_arr [$i ++] = $pdproject;
			}
		} else {
			$fullProject = $this->getProject ( $projects->projectInfo->shortCode );
			$proj_arr [0] = $fullProject;
		}

		return $proj_arr;
	}

	private function getSubmission($submissionTicket){
		$findSubmissionByTicketRequest = new findByTicket ();

		$findSubmissionByTicketRequest->ticket = $submissionTicket;

		$submission = $this->submissionService->findByTicket ( $findSubmissionByTicketRequest )->return;
		if (isset($submission)) {
			return $submission;
		} else {
			throw new Exception("Invalid submission ticket");
		}
	}

	/**
	 * Get Submission ticket if a submission has been initialized.
	 *
	 * @return Submission ticket
	 */
	function getSubmissionTicket() {
		if (isset($this->submission) && isset($this->submission->ticket)) {
			return $this->submission->ticket;
		} else {
			throw new Exception("Submission not initialized");
		}
	}

	/**
	 * Get Submission name for specified ticket.
	 *
	 * @param
	 *        	$submissionTicket
	 *        	Submission ticket
	 * @return Submission name for the specified ticket.
	 */
	function getSubmissionName($submissionTicket) {
		$submission = $this->getSubmission($submissionTicket);
		if (isset($submission)) {
			return $submission->submissionInfo->name;
		} else {
			throw new Exception("Invalid submission ticket");
		}
	}

	/**
	 * Get Submission ID for specified ticket.
	 *
	 * @param
	 *        	$submissionTicket
	 *        	Submission ticket
	 * @return Submission ID for the specified ticket.
	 */
	function getSubmissionId($submissionTicket) {
		$submission = $this->getSubmission($submissionTicket);
		if (isset($submission)) {
			return $submission->submissionId;
		} else {
			throw new Exception("Invalid submission ticket");
		}
	}

	/**
	 * Get Submission Status for specified ticket.
	 *
	 * @param
	 *        	$submissionTicket
	 *        	Submission ticket
	 * @return Status object with string name and integer value
	 */
	function getSubmissionStatus($submissionTicket) {
		$submission = $this->getSubmission($submissionTicket);
		if (isset($submission)) {
			return $submission->status;
		} else {
			throw new Exception("Invalid submission ticket");
		}
	}

	/**
	 * Cancel document for specified target language
	 *
	 * @param
	 *        	$documentTicket
	 *        	Document ticket to cancel
	 * @param
	 *        	$locale
	 *        	Target locale to cancel
	 */
	function cancelTargetByDocumentTicket($documentTicket, $locale) {
			$cancelDocumentRequest = new cancelTargetByDocumentId ();
			$dticket = new DocumentTicket ();
			$dticket->ticketId = $documentTicket;
			$cancelDocumentRequest->documentId = $dticket;
			$cancelDocumentRequest->targetLocale = $locale;
			return $this->targetService->cancelTargetByDocumentId ( $cancelDocumentRequest );

	}

	/**
	 * Cancel target
	 *
	 * @param
	 *        	$targetTicket
	 *        	Target ticket to cancel
	 */
	function cancelTarget($targetTicket) {
		$cancelTargetRequest = new cancelTarget ();
		$cancelTargetRequest->targetId = $targetTicket;
		return $this->targetService->cancelTarget ( $cancelTargetRequest );
	}

	/**
	 * Cancel Submission for all languages
	 *
	 * @param
	 *        	$submissionTicket
	 *        	Submission ticket to cancel
	 * @param
	 *        	$comment
	 *        	[OPTIONAL] Cancellation comment
	 */
	function cancelSubmission($submissionTicket, $comment = NULL) {
		if ($comment == NULL) {
			$cancelSubmissionRequest = new cancelSubmission ();
			$cancelSubmissionRequest->submissionId = $submissionTicket;
			return $this->submissionService->cancelSubmission ( $cancelSubmissionRequest )->return;
		} else {
			$cancelSubmissionRequest = new cancelSubmissionWithComment ();
			$cancelSubmissionRequest->comment = $comment;
			$cancelSubmissionRequest->submissionId = $submissionTicket;
			return $this->submissionService->cancelSubmissionWithComment ( $cancelSubmissionRequest )->return;
		}
	}

	/**
	 * Downloads target document from PD
	 *
	 * @param
	 *        	$ticket
	 *        	Target ticket to download
	 */
	function downloadTarget($ticket) {
		$downloadTargetResourceRequest = new downloadTargetResource ();
		$downloadTargetResourceRequest->targetId = $ticket;
		$repositoryItem = $this->targetService->downloadTargetResource ( $downloadTargetResourceRequest )->return;

		return $repositoryItem->data->_;
	}

	function findAvailableWorkflowInfosForClaim($limit){
		return $this->workflowService->findAvailableWorkflowInfosForClaim($limit)->return;
	}

	function findAvailableWorkflowInfosForDownload($limit){
		return $this->workflowService->findAvailableWorkflowInfosForDownload($limit)->return;
	}

	/**
	 * Get cancelled targets for submission(s)
	 *
	 * @param
	 *        	$submissionTickets
	 *        	Submission ticket or array of submission tickets
	 * @param
	 *        	$maxResults
	 *        	Maximum number of cancelled targets to return. This
	 *        	configuration is to avoid time-outs in case the number of
	 *        	targets is very large.
	 * @return Array of cancelled PDTarget
	 */
	function getCancelledTargetsBySubmissions($submissionTickets, $maxResults) {
		$getCancelledTargetsBySubmissionRequest = new getCanceledTargetsBySubmissions ();
		$getCancelledTargetsBySubmissionRequest->maxResults = $maxResults;
		$getCancelledTargetsBySubmissionRequest->submissionTickets = is_array($submissionTickets)?$submissionTickets:array($submissionTickets);

		$cancelledTargets = $this->targetService->getCanceledTargetsBySubmissions ( $getCancelledTargetsBySubmissionRequest )->return;

		return $this->_convertTargetsToInternal ( $cancelledTargets );
	}

	/**
	 * Get cancelled targets for all projects
	 *
	 * @param
	 *        	$maxResults
	 *        	Maximum number of cancelled targets to return. This
	 *        	configuration is to avoid time-outs in case the number of
	 *        	targets is very large.
	 * @return Array of cancelled PDTarget
	 */
	function getCancelledTargets($maxResults) {
		$projects = $this->getProjects ();
		$tickets = array ();
		$i = 0;
		foreach ( $projects as $project ) {
			$tickets [$i ++] = $project->ticket;
		}
		$getCanceledTargetsByProjectsRequest = new getCanceledTargetsByProjects ();
		$getCanceledTargetsByProjectsRequest->projectTickets = $tickets;
		$getCanceledTargetsByProjectsRequest->maxResults = $maxResults;

		$cancelledTargets = $this->targetService->getCanceledTargetsByProjects ( $getCanceledTargetsByProjectsRequest )->return;

		return $this->_convertTargetsToInternal ( $cancelledTargets );
	}

	/**
	 * Get cancelled targets for document(s)
	 *
	 * @param
	 *        	$documentTickets
	 *        	Document ticket or array of document tickets
	 * @param
	 *        	$maxResults
	 *        	Maximum number of cancelled targets to return. This
	 *        	configuration is to avoid time-outs in case the number of
	 *        	targets is very large.
	 * @return Array of cancelled PDTarget
	 */
	function getCancelledTargetsByDocuments($documentTickets, $maxResults) {
		$getCancelledTargetsByDocumentRequest = new getCanceledTargetsByDocuments ();
		$getCancelledTargetsByDocumentRequest->maxResults = $maxResults;
		$getCancelledTargetsByDocumentRequest->documentTickets = is_array($documentTickets)?$documentTickets:array($documentTickets);

		$cancelledTargets = $this->targetService->getCanceledTargetsByDocuments ( $getCancelledTargetsByDocumentRequest )->return;

		return $this->_convertTargetsToInternal ( $cancelledTargets );
	}

	/**
	 * Get cancelled targets for project(s)
	 *
	 * @param
	 *        	$projectTickets
	 *        	PDProject tickets or array of PDProject tickets for which cancelled targets are requested
	 * @param
	 *        	$maxResults
	 *        	Maximum number of completed targets to return in this call
	 * @return Array of cancelled PDTarget
	 */
	function getCancelledTargetsByProjects($projectTickets, $maxResults) {
		$getCancelledTargetsByProjectsRequest = new getCancelledTargetsByProjects ();

		$getCancelledTargetsByProjectsRequest->projectTickets = is_array($projectTickets)?$projectTickets:array($projectTickets);;
		$getCancelledTargetsByProjectsRequest->maxResults = $maxResults;

		$cancelledTargets = $this->targetService->getCancelledTargetsByProjects ( $getCancelledTargetsByProjectsRequest )->return;

		return $this->_convertTargetsToInternal ( $cancelledTargets );
	}

	/**
	 * Get completed targets for project(s)
	 *
	 * @param
	 *        	$projectTickets
	 *        	PDProject tickets or array of PDProject tickets for which completed targets are requested
	 * @param
	 *        	$maxResults
	 *        	Maximum number of completed targets to return in this call
	 * @return Array of completed PDTarget
	 */
	function getCompletedTargetsByProjects($projectTickets, $maxResults) {
		$getCompletedTargetsByProjectsRequest = new getCompletedTargetsByProjects ();

		$getCompletedTargetsByProjectsRequest->projectTickets = is_array($projectTickets)?$projectTickets:array($projectTickets);;
		$getCompletedTargetsByProjectsRequest->maxResults = $maxResults;

		$completedTargets = $this->targetService->getCompletedTargetsByProjects ( $getCompletedTargetsByProjectsRequest )->return;

		return $this->_convertTargetsToInternal ( $completedTargets );
	}

	/**
	 * Get completed targets for specified PDProject
	 *
	 * @param
	 *        	$project
	 *        	PDProject for which completed targets are requested
	 * @param
	 *        	$maxResults
	 *        	Maximum number of completed targets to return in this call
	 * @return Array of completed PDTarget
	 */
	function getCompletedTargetsByProject($project, $maxResults) {
		return $this->getCompletedTargetsByProjects(array ($project->ticket), $maxResults);
	}

	/**
	 * Get completed targets for all projects
	 *
	 * @param
	 *        	$maxResults
	 *        	Maximum number of completed targets to return in this call
	 * @return Array of completed PDTarget
	 */
	function getCompletedTargets($maxResults) {
		$projects = $this->getProjects ();
		$tickets = array ();
		$i = 0;
		foreach ( $projects as $project ) {
			$tickets [$i ++] = $project->ticket;
		}

		$getCompletedTargetsByProjectsRequest = new getCompletedTargetsByProjects ();

		$getCompletedTargetsByProjectsRequest->projectTickets = $tickets;
		$getCompletedTargetsByProjectsRequest->maxResults = $maxResults;

		$completedTargets = $this->targetService->getCompletedTargetsByProjects ( $getCompletedTargetsByProjectsRequest )->return;

		return $this->_convertTargetsToInternal ( $completedTargets );
	}

	/**
	 * Get completed targets for submission(s)
	 *
	 * @param
	 *        	$submissionTicket
	 *        	Submission ticket or array of tickets for which completed targets are requested
	 * @param
	 *        	$maxResults
	 *        	Maximum number of completed targets to return in this call
	 * @return Array of completed PDTarget
	 */
	function getCompletedTargetsBySubmission($submissionTickets, $maxResults) {
		$getCompletedTargetsBySubmissionsRequest = new getCompletedTargetsBySubmissions ();
		$getCompletedTargetsBySubmissionsRequest->submissionTickets = is_array($submissionTickets)?$submissionTickets:array($submissionTickets);
		$getCompletedTargetsBySubmissionsRequest->maxResults = $maxResults;

		$completedTargets = $this->targetService->getCompletedTargetsBySubmissions ( $getCompletedTargetsBySubmissionsRequest )->return;

		return $this->_convertTargetsToInternal ( $completedTargets );
	}

	/**
	 * Get completed targets by document ticket(s)
	 *
	 * @param
	 *        	$documentTickets
	 *        	Document ticket or array of tickets for which completed targets are requested
	 * @param
	 *        	$maxResults
	 *        	Maximum number of completed targets to return in this call
	 * @return Array of completed PDTarget
	 */
	function getCompletedTargetsByDocument($documentTickets, $maxResults) {
		$getCompletedTargetsByDocumentsRequest = new getCompletedTargetsByDocuments ();
		$getCompletedTargetsByDocumentsRequest->documentTickets = is_array($documentTickets)?$documentTickets:array($documentTickets);
		$getCompletedTargetsByDocumentsRequest->maxResults = $maxResults;

		$completedTargets = $this->targetService->getCompletedTargetsByDocuments ( $getCompletedTargetsByDocumentsRequest )->return;

		return $this->_convertTargetsToInternal ( $completedTargets );
	}

	function getPreliminaryTargets($submissionWorkflowInfo){
		$repositoryItems = array ();
		// 1. Claim
		$workflowRequestForClaim = $this->_createWorkflowRequest($submissionWorkflowInfo);

		$this->workflowService->claim ( $workflowRequestForClaim );

		$submissionTickets = array();
		$submissionTickets[0] = $submissionWorkflowInfo->submissionTicket;

		// 2. Wait until all claimed become available for download
		$availableSubmissionWorkflowInfosForDownload = $this->workflowService->findAvailableWorkflowInfosForDownloadBySubmissionTickets($submissionTickets)->return;
		while(count($availableSubmissionWorkflowInfosForDownload) != 1){
			sleep(DELAY_TIME);
			$availableSubmissionWorkflowInfosForDownload = $this->workflowService->findAvailableWorkflowInfosForDownloadBySubmissionTickets($submissionTickets)->return;
		}

		// 3. Send download preview requests
		$workflowRequestTickets = array();
		foreach ($availableSubmissionWorkflowInfosForDownload as &$availableSubmissionWorkflowInfo) {
		    $workflowRequestForDownload = $this->_createWorkflowRequest($availableSubmissionWorkflowInfo);
		    $workflowRequestTicket = $this->workflowService->downloadPreview($workflowRequestForDownload)->return;
		    array_push($workflowRequestTickets, $workflowRequestTicket);
		}

		// 4. Download
		while (count($workflowRequestTickets) > 0) {
			foreach ($workflowRequestTickets as &$workflowRequestTicket) {
				$downloadActionResult = $this->workflowService->checkDownloadAction($workflowRequestTicket)->return;
				if ($downloadActionResult->processingFinished->booleanValue) {
					$repositoryItem = $downloadActionResult->repositoryItem;
					array_push($repositoryItems, $repositoryItem);
					unset($workflowRequestTickets[$workflowRequestTicket]);
				}
			}
		}

		return $repositoryItems;
	}

	/**
	 * Get Unstarted Submissions.
	 *
	 * @param
	 *        	$project
	 *        	PDProject
	 * @return Array of PDSubmission that have not been started.
	 */
	function getUnstartedSubmissions($project) {
		$submissions = array ();
		$creatingSubmissions = $this->submissionService->findCreatingSubmissionsByProjectShortCode($project->shortCode)->return;
		foreach ($creatingSubmissions as &$creatingSubmission){
			$sub = new Submission();
			$sub->ticket = $creatingSubmission->ticket;
			$sub->name = $creatingSubmission->submissionInfo->name;
			array_push($submissions , $sub);
		}
		return $submissions;
	}

	/**
	 * Initialize a new Submission
	 *
	 * @param
	 *        	$submission
	 *        	PDSubmission configuration to initialize a new submission
	 */
	function initSubmission($submission) {
		$this->_validateSubmission ( $submission );

		$this->submission = $submission;
		$this->submission->ticket = "";
	}

	/**
	 * Validate Submission submitter
	 *
	 * @param
	 *        	$shortCode
	 *        	Project shortcode
	 * @param
	 *        	$submitter
	 *        	Username to validate
	 */
	function isSubmitterValid($shortCode, $newSubmitter){
		$getSubmittersRequest = new getSubmitters ();
		$getSubmittersRequest->projectShortCode = $shortCode;
		$submitters = $this->userProfileService->getSubmitters ( $getSubmittersRequest )->return;
		if(isset($submitters)){
			foreach ($submitters as &$submitter){
				if(isset($submitter)){
					$info = $submitter;
					if(isset($submitter->userInfo)){
						$info = $submitter->userInfo;
					}
					if($info->userName == $newSubmitter && ($info->enabled=='1' || $info->enabled==1 || $info->enabled=='true' || $info->enabled==true)){
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Sends confirmation that the target resources was downloaded successfully
	 * by the customer.
	 *
	 * @param
	 *        	$ticket
	 *        	Downloaded target ticket
	 */
	function sendDownloadConfirmation($ticket) {
		$sendDownloadConfirmationRequest = new sendDownloadConfirmation ();

		$sendDownloadConfirmationRequest->targetId = $ticket;

		return $this->targetService->sendDownloadConfirmation ( $sendDownloadConfirmationRequest )->return;
	}

	/**
	 *
	 * Start Submission
	 *
	 * @return Submission ticket
	 */
	function startSubmission($modifiedSubmissionInfo = null) {
		if (! isset ( $this->submission ) || ! isset ( $this->submission->project ) || ! isset ( $this->submission->name )) {
			throw new Exception ( "Please initialize submission first." );
		}

		if (! isset ( $this->submission->ticket ) || $this->submission->ticket == "") {
			throw new Exception ( "Please upload a translatable document first." );
		}
        $submissionInfo = $this->_createSubmissionInfo ();
		$startSubmissionRequest = new startSubmission ();
		$startSubmissionRequest->submissionId = $this->submission->ticket;
		$startSubmissionRequest->submissionInfo = $submissionInfo;

		$this->submissionService->startSubmission ( $startSubmissionRequest );

		$submissionTicket = $this->submission->ticket;
		$this->submission = NULL;

		return $submissionTicket;
	}

	/**
	 *
	 * Upload reference file for submission
	 *
	 * @param
	 *        	$referenceDocument
	 *        	PDReferenceDocument
	 */
	function uploadReference($referenceDocument) {
		if (! isset ( $referenceDocument ) || ! isset ( $referenceDocument->data )) {
			throw new Exception ( "Document is empty" );
		}
		if (! isset ( $referenceDocument->name )) {
			throw new Exception ( "Document name not set" );
		}

		if (! isset ( $this->submission ) || ! isset ( $this->submission->ticket )) {
			throw new Exception ( "Submission not initialized." );
		}
		if ($this->submission->ticket == "") {
			throw new Exception ( "Invalid submission ticket. Please upload a translatable document before attempting to upload reference documents." );
		}

		$uploadReferenceRequest = new uploadReference ();
		$uploadReferenceRequest->data = $referenceDocument->data;
		$uploadReferenceRequest->submissionId = $this->submission->ticket;
		$uploadReferenceRequest->resourceInfo = $referenceDocument->getResourceInfo ();

		return $this->submissionService->uploadReference ( $uploadReferenceRequest )->return;
	}

	/**
	 * Uploads the document to project director for translation
	 *
	 * @param
	 *        	$document
	 *        	PDDocument that requires translation
	 * @return Document ticket
	 */
	function uploadTranslatable($document) {
		if (! isset ( $this->submission ) || ! isset ( $this->submission->ticket )) {
			throw new Exception ( "Submission not initialized." );
		}
		$this->_validateDocument ( $document );

		$documentInfo = $document->getDocumentInfo ( $this->submission );
		$resourceInfo = $document->getResourceInfo ();

		$submitDocumentWithBinaryResourceRequest = new submitDocumentWithBinaryResource ();

		$submitDocumentWithBinaryResourceRequest->documentInfo = $documentInfo;
		$submitDocumentWithBinaryResourceRequest->resourceInfo = $resourceInfo;
		$submitDocumentWithBinaryResourceRequest->data = $document->data;

		$documentTicket = $this->documentService->submitDocumentWithBinaryResource ( $submitDocumentWithBinaryResourceRequest )->return;
		if (isset ( $documentTicket )) {
			$this->submission->ticket = $documentTicket->submissionTicket;
		}

		return $documentTicket->ticketId;
	}

	/**
	 * Uploads preliminary delivery file to project director
	 *
	 * @param
	 *        	$fileName
	 *        	Filename that requires translation
	* @param
	 *        	$data
	 *        	File data (String)
	 * @return Response message
	 */
	function uploadTranslationKit($fileName, $data){
		$result = "";
		$resourceInfo = new ResourceInfo();
		$resourceInfo->name = $fileName;
		$resourceInfo->size = strlen ( $data );

		// Upload file
		$workflowRequestTicket = $this->workflowService->upload($resourceInfo, $data)->return;

		// Wait until upload is done, or print error message if it failed
		$uploadFinished = false;
		while (!$uploadFinished) {
			// Create delay between two checkUploadAction calls
			sleep(DELAY_TIME);

			$uploadActionResult = $this->workflowService->checkUploadAction($workflowRequestTicket);

			$uploadFinished = $uploadActionResult->processingFinished->booleanValue;
			if ($uploadFinished && isset($uploadActionResult->messages)) {
				foreach($uploadActionResult->messages as &$message){
					$result = $result + $message+";";
				}
			}
		}

		return $result;
	}

	public function setAdaptorName($adaptorName){
	    $this->adaptorName = $adaptorName;
    }
    public function setAdaptorVersion($adaptorVersion){
	    $this->adaptorVersion = $adaptorVersion;
    }
    public function setClientVersion($clientVersion){
	    $this->clientVersion = $clientVersion;
    }
}

?>
