<?php
require_once 'pd_ws/client/ProjectService_4130.php';
require_once 'pd_ws/client/SubmissionService_4130.php';
require_once 'pd_ws/client/TargetService_4130.php';
require_once 'pd_ws/client/DocumentService_4130.php';
require_once 'pd_ws/client/UserProfileService_4130.php';
require_once 'pd_ws/client/WorkflowService_4130.php';
require_once 'model/Project.inc.php';
require_once 'model/Submission.inc.php';
require_once 'model/Target.inc.php';
define ( 'GL_WSDL_PATH', __DIR__ . '/pd_ws/wsdl/' );
define ( 'USER_PROFILE_SERVICE_WSDL', GL_WSDL_PATH . 'UserProfileService_4130.wsdl' );
define ( 'SUBMISSION_SERVICE_WSDL', GL_WSDL_PATH . 'SubmissionService_4130.wsdl' );
define ( 'WORKFLOW_SERVICE_WSDL', GL_WSDL_PATH . 'WorkflowService_4130.wsdl' );
define ( 'DOCUMENT_SERVICE_WSDL', GL_WSDL_PATH . 'DocumentService_4130.wsdl' );
define ( 'PROJECT_SERVICE_WSDL', GL_WSDL_PATH . 'ProjectService_4130.wsdl' );
define ( 'TARGET_SERVICE_WSDL', GL_WSDL_PATH . 'TargetService_4130.wsdl' );
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
	
	/**
	 *
	 * Initialize Project Director connector
	 *
	 * @param
	 *        	$connectionConfig
	 *        	PDConfig - Project Director Connection Configuration
	 */
	function GLExchange($connectionConfig) {
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
	private function _setConnectionConfig($connectionConfig) {
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
		
		$security = '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">' . '<wsse:UsernameToken xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" wsu:Id="UsernameToken-1">' . '<wsse:Username>' . $this->pdConfig->username . '</wsse:Username>' . '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' . $this->pdConfig->password . '</wsse:Password>' . '</wsse:UsernameToken></wsse:Security>';
		$userAgent = '<commons:userAgent xmlns:commons="http://commons.ws.projectdirector.gs4tr.org">' . $this->pdConfig->userAgent . '</commons:userAgent>';
		
		$header = array ();
		$header [] = new SoapHeader ( "Security", 'Security', new SoapVar ( $security, XSD_ANYXML ), true );
		$header [] = new SoapHeader ( "userAgent", 'userAgent', new SoapVar ( $userAgent, XSD_ANYXML ), true );

		$this->projectService = new ProjectService_4130 ( PROJECT_SERVICE_WSDL, array_merge ( array (
				'location' => $this->pdConfig->url . '/services/ProjectService' 
		), $proxyConfig ), $header );
		$this->submissionService = new SubmissionService_4130 ( SUBMISSION_SERVICE_WSDL, array_merge ( array (
				'location' => $this->pdConfig->url . '/services/SubmissionService' 
		), $proxyConfig ), $header );
		$this->workflowService = new WorkflowService_4130 ( WORKFLOW_SERVICE_WSDL, array_merge ( array (
				'location' => $this->pdConfig->url . '/services/WorkflowService' 
		), $proxyConfig ), $header );
		$this->targetService = new TargetService_4130 ( TARGET_SERVICE_WSDL, array_merge ( array (
				'location' => $this->pdConfig->url . '/services/TargetService' 
		), $proxyConfig ), $header );
		$this->documentService = new DocumentService_4130 ( DOCUMENT_SERVICE_WSDL, array_merge ( array (
				'location' => $this->pdConfig->url . '/services/DocumentService' 
		), $proxyConfig ), $header );
		$this->userProfileService = new UserProfileService_4130 ( USER_PROFILE_SERVICE_WSDL, array_merge ( array (
				'location' => $this->pdConfig->url . '/services/UserProfileService' 
		), $proxyConfig ), $header );
		
		try {
			$getUserProjectsRequest = new getUserProjects ();
			$getUserProjectsRequest->isSubProjectIncluded = TRUE;
			$projects = $this->projectService->getUserProjects ( $getUserProjectsRequest )->return;
		} catch ( Exception $ex ) {
			throw new Exception ( "Invalid config. " . $ex->getMessage () );
		}
	}
	private function _convertTargetsToInternal($targets) {
		$targets_arr = array ();
		if(count($targets)>0){
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
			$submissionInfo->$submissionCustomFields = $attributes;
		}
		
		if (isset ( $this->submission->workflow ) && $this->submission->workflow->ticket != "") {
			$submissionInfo->workflowDefinitionTicket = $this->submission->workflow->ticket;
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
						foreach ( $submission->customAttributes as $submissionCustomAttribute ) {
							if ($submissionCustomAttribute->name == $projectCustomAttribute->name) {
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
		$findSubmissionByTicketRequest = new findByTicket ();
		
		$findSubmissionByTicketRequest->ticket = $submissionTicket;
		
		$submission = $this->submissionService->findByTicket ( $findSubmissionByTicketRequest )->return;
		if (isset($submission)) {
			return $submission->submissionInfo->name;
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
	 * Get cancelled targets for a submission
	 *
	 * @param
	 *        	$submissionTicket
	 *        	Submission ticket
	 * @param
	 *        	$maxResults
	 *        	Maximum number of cancelled targets to return. This
	 *        	configuration is to avoid time-outs in case the number of
	 *        	targets is very large.
	 * @return Array of cancelled PDTarget
	 */
	function getCancelledTargetsBySubmission($submissionTickets, $maxResults) {
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
	 * Get completed targets for specified project
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
		$getCompletedTargetsByProjectsRequest = new getCompletedTargetsByProjects ();
		
		$getCompletedTargetsByProjectsRequest->projectTickets = array (
				$project->ticket 
		);
		$getCompletedTargetsByProjectsRequest->maxResults = $maxResults;
		
		$completedTargets = $this->targetService->getCompletedTargetsByProjects ( $getCompletedTargetsByProjectsRequest )->return;
		
		return $this->_convertTargetsToInternal ( $completedTargets );
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
	 * Get completed targets for submission
	 *
	 * @param
	 *        	$submissionTicket
	 *        	Submission ticket for which completed targets are requested
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
	function startSubmission() {
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
	
}

?>
