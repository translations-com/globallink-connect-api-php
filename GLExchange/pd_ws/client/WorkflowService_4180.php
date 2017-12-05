<?php

if (!class_exists("Notification")) {
/**
 * Notification
 */
class Notification {
	/**
	 * @access public
	 * @var string
	 */
	public $errorMessage;
	/**
	 * @access public
	 * @var Date
	 */
	public $notificationDate;
	/**
	 * @access public
	 * @var NotificationPriority
	 */
	public $notificationPriority;
	/**
	 * @access public
	 * @var string
	 */
	public $notificationText;
}}

if (!class_exists("NotificationPriority")) {
/**
 * NotificationPriority
 */
class NotificationPriority {
	/**
	 * @access public
	 * @var string
	 */
	public $notificationPriorityName;
}}

if (!class_exists("Announcement")) {
/**
 * Announcement
 */
class Announcement {
	/**
	 * @access public
	 * @var string
	 */
	public $announcementText;
	/**
	 * @access public
	 * @var Date
	 */
	public $date;
}}

if (!class_exists("Batch")) {
/**
 * Batch
 */
class Batch {
	/**
	 * @access public
	 * @var string
	 */
	public $name;
	/**
	 * @access public
	 * @var string[]
	 */
	public $targetLanguages;
	/**
	 * @access public
	 * @var WorkflowDefinition
	 */
	public $workflowDefinition;
}}

if (!class_exists("ContentMonitorPluginInfo")) {
/**
 * ContentMonitorPluginInfo
 */
class ContentMonitorPluginInfo {
	/**
	 * @access public
	 * @var string
	 */
	public $pluginId;
	/**
	 * @access public
	 * @var string
	 */
	public $pluginName;
}}

if (!class_exists("Date")) {
/**
 * Date
 */
class Date {
	/**
	 * @access public
	 * @var boolean
	 */
	public $critical;
	/**
	 * @access public
	 * @var integer
	 */
	public $date;
}}

if (!class_exists("Document")) {
/**
 * Document
 */
class Document {
	/**
	 * @access public
	 * @var DocumentGroup
	 */
	public $documentGroup;
	/**
	 * @access public
	 * @var DocumentInfo
	 */
	public $documentInfo;
	/**
	 * @access public
	 * @var string
	 */
	public $id;
	/**
	 * @access public
	 * @var Language
	 */
	public $sourceLanguage;
	/**
	 * @access public
	 * @var integer
	 */
	public $sourceWordCount;
	/**
	 * @access public
	 * @var string
	 */
	public $ticket;
}}

if (!class_exists("DocumentGroup")) {
/**
 * DocumentGroup
 */
class DocumentGroup {
	/**
	 * @access public
	 * @var string
	 */
	public $classifier;
	/**
	 * @access public
	 * @var Document[]
	 */
	public $documents;
	/**
	 * @access public
	 * @var string
	 */
	public $mimeType;
	/**
	 * @access public
	 * @var Submission
	 */
	public $submission;
}}

if (!class_exists("DocumentInfo")) {
/**
 * DocumentInfo
 */
class DocumentInfo {
	/**
	 * @access public
	 * @var DocumentInfo[]
	 */
	public $childDocumentInfos;
	/**
	 * @access public
	 * @var string
	 */
	public $clientIdentifier;
	/**
	 * @access public
	 * @var Date
	 */
	public $dateRequested;
	/**
	 * @access public
	 * @var string
	 */
	public $instructions;
	/**
	 * @access public
	 * @var Metadata[]
	 */
	public $metadata;
	/**
	 * @access public
	 * @var string
	 */
	public $name;
	/**
	 * @access public
	 * @var string
	 */
	public $projectTicket;
	/**
	 * @access public
	 * @var string
	 */
	public $sourceLocale;
	/**
	 * @access public
	 * @var string
	 */
	public $submissionTicket;
	/**
	 * @access public
	 * @var TargetInfo[]
	 */
	public $targetInfos;
	/**
	 * @access public
	 * @var integer
	 */
	public $wordCount;
}}

if (!class_exists("DocumentPagedList")) {
/**
 * DocumentPagedList
 */
class DocumentPagedList {
	/**
	 * @access public
	 * @var Document[]
	 */
	public $elements;
	/**
	 * @access public
	 * @var PagedListInfo
	 */
	public $pagedListInfo;
	/**
	 * @access public
	 * @var Task[]
	 */
	public $tasks;
	/**
	 * @access public
	 * @var integer
	 */
	public $totalCount;
}}

if (!class_exists("DocumentSearchRequest")) {
/**
 * DocumentSearchRequest
 */
class DocumentSearchRequest {
	/**
	 * @access public
	 * @var string[]
	 */
	public $projectTickets;
	/**
	 * @access public
	 * @var string
	 */
	public $sourceLocaleId;
	/**
	 * @access public
	 * @var string
	 */
	public $submissionTicket;
}}

if (!class_exists("DocumentTicket")) {
/**
 * DocumentTicket
 */
class DocumentTicket {
	/**
	 * @access public
	 * @var string
	 */
	public $submissionTicket;
	/**
	 * @access public
	 * @var string
	 */
	public $ticketId;
}}

if (!class_exists("EntityTypeEnum")) {
/**
 * EntityTypeEnum
 */
class EntityTypeEnum {
	/**
	 * @access public
	 * @var string
	 */
	public $name;
	/**
	 * @access public
	 * @var integer
	 */
	public $value;
}}

if (!class_exists("FileFormatProfile")) {
/**
 * FileFormatProfile
 */
class FileFormatProfile {
	/**
	 * @access public
	 * @var boolean
	 */
	public $configurable;
	/**
	 * @access public
	 * @var boolean
	 */
	public $isDefault;
	/**
	 * @access public
	 * @var string
	 */
	public $mimeType;
	/**
	 * @access public
	 * @var string
	 */
	public $pluginId;
	/**
	 * @access public
	 * @var string
	 */
	public $pluginName;
	/**
	 * @access public
	 * @var string
	 */
	public $profileName;
	/**
	 * @access public
	 * @var WorkflowDefinition
	 */
	public $targetWorkflowDefinition;
	/**
	 * @access public
	 * @var string
	 */
	public $ticket;
}}

if (!class_exists("FileFormatProgressData")) {
/**
 * FileFormatProgressData
 */
class FileFormatProgressData {
	/**
	 * @access public
	 * @var Date
	 */
	public $dateCompleted;
	/**
	 * @access public
	 * @var integer
	 */
	public $fileCount;
	/**
	 * @access public
	 * @var string
	 */
	public $fileFormatName;
	/**
	 * @access public
	 * @var FileProgressData
	 */
	public $fileProgressData;
	/**
	 * @access public
	 * @var string
	 */
	public $jobTicket;
	/**
	 * @access public
	 * @var Date
	 */
	public $workflowDueDate;
	/**
	 * @access public
	 * @var string
	 */
	public $workflowStatus;
}}

if (!class_exists("FileProgressData")) {
/**
 * FileProgressData
 */
class FileProgressData {
	/**
	 * @access public
	 * @var integer
	 */
	public $numberOfAvailableFiles;
	/**
	 * @access public
	 * @var integer
	 */
	public $numberOfCanceledFiles;
	/**
	 * @access public
	 * @var integer
	 */
	public $numberOfCompletedFiles;
	/**
	 * @access public
	 * @var integer
	 */
	public $numberOfDeliveredFiles;
	/**
	 * @access public
	 * @var integer
	 */
	public $numberOfFailedFiles;
	/**
	 * @access public
	 * @var integer
	 */
	public $numberOfInProcessFiles;
	/**
	 * @access public
	 * @var integer
	 */
	public $overallProgressPercent;
}}

if (!class_exists("FuzzyTmStatistics")) {
/**
 * FuzzyTmStatistics
 */
class FuzzyTmStatistics {
	/**
	 * @access public
	 * @var string
	 */
	public $fuzzyName;
	/**
	 * @access public
	 * @var integer
	 */
	public $wordCount;
}}

if (!class_exists("ItemFolderEnum")) {
/**
 * ItemFolderEnum
 */
class ItemFolderEnum {
	/**
	 * @access public
	 * @var integer
	 */
	public $value;
}}

if (!class_exists("ItemStatusEnum")) {
/**
 * ItemStatusEnum
 */
class ItemStatusEnum {
	/**
	 * @access public
	 * @var string
	 */
	public $name;
	/**
	 * @access public
	 * @var integer
	 */
	public $value;
}}

if (!class_exists("Metadata")) {
/**
 * Metadata
 */
class Metadata {
	/**
	 * @access public
	 * @var string
	 */
	public $key;
	/**
	 * @access public
	 * @var string
	 */
	public $value;
}}

if (!class_exists("Language")) {
/**
 * Language
 */
class Language {
	/**
	 * @access public
	 * @var string
	 */
	public $locale;
	/**
	 * @access public
	 * @var string
	 */
	public $value;
}}

if (!class_exists("LanguageDirection")) {
/**
 * LanguageDirection
 */
class LanguageDirection {
	/**
	 * @access public
	 * @var Language
	 */
	public $sourceLanguage;
	/**
	 * @access public
	 * @var Language
	 */
	public $targetLanguage;
}}

if (!class_exists("LanguageDirectionModel")) {
/**
 * LanguageDirectionModel
 */
class LanguageDirectionModel {
	/**
	 * @access public
	 * @var Date
	 */
	public $dateCompleted;
	/**
	 * @access public
	 * @var integer
	 */
	public $fileCount;
	/**
	 * @access public
	 * @var FileFormatProgressData[]
	 */
	public $fileFormatProgressData;
	/**
	 * @access public
	 * @var FileProgressData
	 */
	public $fileProgress;
	/**
	 * @access public
	 * @var Language
	 */
	public $sourceLanguage;
	/**
	 * @access public
	 * @var Language
	 */
	public $targetLanguage;
	/**
	 * @access public
	 * @var Date
	 */
	public $workflowDueDate;
	/**
	 * @access public
	 * @var string
	 */
	public $workflowStatus;
}}

if (!class_exists("LanguagePhaseInfo")) {
/**
 * LanguagePhaseInfo
 */
class LanguagePhaseInfo {
	/**
	 * @access public
	 * @var Date
	 */
	public $phaseStartDate;
	/**
	 * @access public
	 * @var string[]
	 */
	public $sourceFileList;
	/**
	 * @access public
	 * @var TmStatistics
	 */
	public $tmStatistics;
}}

if (!class_exists("Organization")) {
/**
 * Organization
 */
class Organization {
	/**
	 * @access public
	 * @var integer
	 */
	public $availableTasks;
	/**
	 * @access public
	 * @var Organization
	 */
	public $parentOrganization;
	/**
	 * @access public
	 * @var OrganizationInfo
	 */
	public $organizationInfo;
	/**
	 * @access public
	 * @var Task[]
	 */
	public $tasks;
	/**
	 * @access public
	 * @var string
	 */
	public $ticket;
}}

if (!class_exists("OrganizationInfo")) {
/**
 * OrganizationInfo
 */
class OrganizationInfo {
	/**
	 * @access public
	 * @var string
	 */
	public $name;
	/**
	 * @access public
	 * @var string
	 */
	public $ticket;
	/**
	 * @access public
	 * @var string
	 */
	public $currencyCode;
	/**
	 * @access public
	 * @var string
	 */
	public $domain;
	/**
	 * @access public
	 * @var string
	 */
	public $theme;
	/**
	 * @access public
	 * @var boolean
	 */
	public $enabled;
}}

if (!class_exists("PagedListInfo")) {
/**
 * PagedListInfo
 */
class PagedListInfo {
	/**
	 * @access public
	 * @var integer
	 */
	public $index;
	/**
	 * @access public
	 * @var integer
	 */
	public $indexesSize;
	/**
	 * @access public
	 * @var integer
	 */
	public $size;
	/**
	 * @access public
	 * @var string
	 */
	public $sortDirection;
	/**
	 * @access public
	 * @var string
	 */
	public $sortProperty;
}}

if (!class_exists("Phase")) {
/**
 * Phase
 */
class Phase {
	/**
	 * @access public
	 * @var Date
	 */
	public $dateEnded;
	/**
	 * @access public
	 * @var Date
	 */
	public $dueDate;
	/**
	 * @access public
	 * @var string
	 */
	public $name;
	/**
	 * @access public
	 * @var ItemStatusEnum
	 */
	public $status;
}}

if (!class_exists("PreviewResult")) {
/**
 * PreviewResult
 */
class PreviewResult {
	/**
	 * @access public
	 * @var string
	 */
	public $message;
	/**
	 * @access public
	 * @var RepositoryItem
	 */
	public $repositoryItem;
}}

if (!class_exists("Priority")) {
/**
 * Priority
 */
class Priority {
	/**
	 * @access public
	 * @var string
	 */
	public $name;
	/**
	 * @access public
	 * @var integer
	 */
	public $value;
}}

if (!class_exists("Project")) {
/**
 * Project
 */
class Project {
	/**
	 * @access public
	 * @var Announcement[]
	 */
	public $announcements;
	/**
	 * @access public
	 * @var ContentMonitorPluginInfo
	 */
	public $contentMonitorPluginInfo;
	/**
	 * @access public
	 * @var WorkflowDefinition
	 */
	public $defaultTargetWorkflowDefinition;
	/**
	 * @access public
	 * @var string
	 */
	public $defaultTargetWorkflowDefinitionTicket;
	/**
	 * @access public
	 * @var FileFormatProfile[]
	 */
	public $fileFormatProfiles;
	/**
	 * @access public
	 * @var boolean[]
	 */
	public $includeSubmissionNameInLocalizationKit;
	/**
	 * @access public
	 * @var Metadata[]
	 */
	public $metadata;
	/**
	 * @access public
	 * @var string
	 */
	public $organizationName;
	/**
	 * @access public
	 * @var ProjectCustomFieldConfiguration[]
	 */
	public $projectCustomFieldConfiguration;
	/**
	 * @access public
	 * @var ProjectInfo
	 */
	public $projectInfo;
	/**
	 * @access public
	 * @var ProjectLanguageDirection[]
	 */
	public $projectLanguageDirections;
	/**
	 * @access public
	 * @var string
	 */
	public $ticket;
	/**
	 * @access public
	 * @var WorkflowDefinition[]
	 */
	public $workflowDefinitions;
}}

if (!class_exists("ProjectInfo")) {
/**
 * ProjectInfo
 */
class ProjectInfo {
	/**
	 * @access public
	 * @var string
	 */
	public $clientIdentifier;
	/**
	 * @access public
	 * @var string
	 */
	public $defaultJobWorkflowDefinitionTicket;
	/**
	 * @access public
	 * @var string
	 */
	public $defaultSubmissionWorkflowDefinitionTicket;
	/**
	 * @access public
	 * @var string
	 */
	public $defaultTargetWorkflowDefinitionTicket;
	/**
	 * @access public
	 * @var boolean
	 */
	public $enabled;
	/**
	 * @access public
	 * @var string
	 */
	public $name;
	/**
	 * @access public
	 * @var string
	 */
	public $shortCode;
}}

if (!class_exists("ProjectLanguage")) {
/**
 * ProjectLanguage
 */
class ProjectLanguage {
	/**
	 * @access public
	 * @var string
	 */
	public $customLocaleCode;
	/**
	 * @access public
	 * @var string
	 */
	public $localeCode;
}}

if (!class_exists("ProjectLanguageDirection")) {
/**
 * ProjectLanguageDirection
 */
class ProjectLanguageDirection {
	/**
	 * @access public
	 * @var Language
	 */
	public $sourceLanguage;
	/**
	 * @access public
	 * @var Language
	 */
	public $targetLanguage;
	/**
	 * @access public
	 * @var boolean
	 */
	public $default;
	/**
	 * @access public
	 * @var boolean
	 */
	public $frequent;
}}

if (!class_exists("ProjectAClient")) {
/**
 * ProjectAClient
 */
class ProjectAClient {
	/**
	 * @access public
	 * @var boolean
	 */
	public $enabled;
	/**
	 * @access public
	 * @var string
	 */
	public $name;
	/**
	 * @access public
	 * @var Organization
	 */
	public $parentOrganization;
	/**
	 * @access public
	 * @var string
	 */
	public $ticket;
}}

if (!class_exists("RepositoryItem")) {
/**
 * RepositoryItem
 */
class RepositoryItem {
	/**
	 * @access public
	 * @var base64Binary
	 */
	public $data;
	/**
	 * @access public
	 * @var ResourceInfo
	 */
	public $resourceInfo;
}}

if (!class_exists("ResourceInfo")) {
/**
 * ResourceInfo
 */
class ResourceInfo {
	/**
	 * @access public
	 * @var string
	 */
	public $classifier;
	/**
	 * @access public
	 * @var string
	 */
	public $clientIdentifier;
	/**
	 * @access public
	 * @var string
	 */
	public $description;
	/**
	 * @access public
	 * @var string
	 */
	public $encoding;
	/**
	 * @access public
	 * @var string
	 */
	public $md5Checksum;
	/**
	 * @access public
	 * @var string
	 */
	public $mimeType;
	/**
	 * @access public
	 * @var string
	 */
	public $name;
	/**
	 * @access public
	 * @var string
	 */
	public $path;
	/**
	 * @access public
	 * @var integer
	 */
	public $resourceInfoId;
	/**
	 * @access public
	 * @var integer
	 */
	public $size;
	/**
	 * @access public
	 * @var ResourceType
	 */
	public $type;
}}

if (!class_exists("ResourceType")) {
/**
 * ResourceType
 */
class ResourceType {
	/**
	 * @access public
	 * @var integer
	 */
	public $value;
}}

if (!class_exists("Submission")) {
/**
 * Submission
 */
class Submission {
	/**
	 * @access public
	 * @var Notification[]
	 */
	public $alerts;
	/**
	 * @access public
	 * @var integer
	 */
	public $availableTasks;
	/**
	 * @access public
	 * @var Batch[]
	 */
	public $batches;
	/**
	 * @access public
	 * @var Date
	 */
	public $dateArchived;
	/**
	 * @access public
	 * @var Date
	 */
	public $dateCompleted;
	/**
	 * @access public
	 * @var Date
	 */
	public $dateCreated;
	/**
	 * @access public
	 * @var Date
	 */
	public $dateEstimated;
	/**
	 * @access public
	 * @var Document[]
	 */
	public $documents;
	/**
	 * @access public
	 * @var Date
	 */
	public $dueDate;
	/**
	 * @access public
	 * @var string
	 */
	public $id;
	/**
	 * @access public
	 * @var string
	 */
	public $owner;
	/**
	 * @access public
	 * @var Project
	 */
	public $project;
	/**
	 * @access public
	 * @var ItemStatusEnum
	 */
	public $status;
	/**
	 * @access public
	 * @var integer
	 */
	public $submissionId;
	/**
	 * @access public
	 * @var SubmissionInfo
	 */
	public $submissionInfo;
	/**
	 * @access public
	 * @var string[]
	 */
	public $submitterFullNames;
	/**
	 * @access public
	 * @var string
	 */
	public $ticket;
	/**
	 * @access public
	 * @var WorkflowDefinition
	 */
	public $workflowDefinition;
}}

if (!class_exists("SubmissionInfo")) {
/**
 * SubmissionInfo
 */
class SubmissionInfo {
	/**
	 * @access public
	 * @var string
	 */
	public $additionalCosts;
	/**
	 * @access public
	 * @var boolean
	 */
	public $autoStartChilds;
	/**
	 * @access public
	 * @var ClaimScopeEnum
	 */
	public $claimScope;
	/**
	 * @access public
	 * @var string
	 */
	public $clientIdentifier;
	/**
	 * @access public
	 * @var Date
	 */
	public $dateRequested;
	/**
	 * @access public
	 * @var string
	 */
	public $internalNotes;
	/**
	 * @access public
	 * @var Metadata[]
	 */
	public $metadata;
	/**
	 * @access public
	 * @var string
	 */
	public $name;
	/**
	 * @access public
	 * @var string
	 */
	public $officeName;
	/**
	 * @access public
	 * @var string
	 */
	public $paClientTicket;
	/**
	 * @access public
	 * @var string
	 */
	public $paJobNumber;
	/**
	 * @access public
	 * @var Priority
	 */
	public $priority;
	/**
	 * @access public
	 * @var string
	 */
	public $projectTicket;
	/**
	 * @access public
	 * @var double
	 */
	public $revenue;
	/**
	 * @access public
	 * @var string
	 */
	public $submissionBackground;
	/**
	 * @access public
	 * @var SubmissionCustomFields[]
	 */
	public $submissionCustomFields;
	/**
	 * @access public
	 * @var string[]
	 */
	public $submitters;
	/**
	 * @access public
	 * @var string
	 */
	public $workflowDefinitionTicket;
}}

if (!class_exists("SubmissionPagedList")) {
/**
 * SubmissionPagedList
 */
class SubmissionPagedList {
	/**
	 * @access public
	 * @var Submission[]
	 */
	public $elements;
	/**
	 * @access public
	 * @var PagedListInfo
	 */
	public $pagedListInfo;
	/**
	 * @access public
	 * @var Task[]
	 */
	public $tasks;
	/**
	 * @access public
	 * @var integer
	 */
	public $totalCount;
}}

if (!class_exists("SimpleSubmissionSearchModel")) {
/**
 * SimpleSubmissionSearchModel
 */
class SimpleSubmissionSearchModel {
	/**
	 * @access public
	 * @var Notification[]
	 */
	public $alerts;
	/**
	 * @access public
	 * @var integer
	 */
	public $availableTasks;
	/**
	 * @access public
	 * @var integer
	 */
	public $budgetStatus;
	/**
	 * @access public
	 * @var ClaimScopeEnum
	 */
	public $claimScope;
	/**
	 * @access public
	 * @var string[]
	 */
	public $customFields;
	/**
	 * @access public
	 * @var Date
	 */
	public $date;
	/**
	 * @access public
	 * @var Date
	 */
	public $dateArchived;
	/**
	 * @access public
	 * @var Date
	 */
	public $dateCompleted;
	/**
	 * @access public
	 * @var Date
	 */
	public $dateStarted;
	/**
	 * @access public
	 * @var Date
	 */
	public $dueDate;
	/**
	 * @access public
	 * @var integer
	 */
	public $fileCount;
	/**
	 * @access public
	 * @var FileProgressData
	 */
	public $fileProgress;
	/**
	 * @access public
	 * @var integer
	 */
	public $gate;
	/**
	 * @access public
	 * @var string
	 */
	public $id;
	/**
	 * @access public
	 * @var string
	 */
	public $instructions;
	/**
	 * @access public
	 * @var string
	 */
	public $officeName;
	/**
	 * @access public
	 * @var UserData[]
	 */
	public $owner;
	/**
	 * @access public
	 * @var string
	 */
	public $paClientName;
	/**
	 * @access public
	 * @var string
	 */
	public $parentSubmissionName;
	/**
	 * @access public
	 * @var string
	 */
	public $parentTicket;
	/**
	 * @access public
	 * @var string
	 */
	public $pmNotes;
	/**
	 * @access public
	 * @var string
	 */
	public $priority;
	/**
	 * @access public
	 * @var string
	 */
	public $projectName;
	/**
	 * @access public
	 * @var string
	 */
	public $projectTicket;
	/**
	 * @access public
	 * @var integer
	 */
	public $quote;
	/**
	 * @access public
	 * @var boolean
	 */
	public $reserved;
	/**
	 * @access public
	 * @var string
	 */
	public $sourceLanguage;
	/**
	 * @access public
	 * @var ItemStatusEnum
	 */
	public $status;
	/**
	 * @access public
	 * @var string
	 */
	public $submissionBackground;
	/**
	 * @access public
	 * @var string
	 */
	public $submissionName;
	/**
	 * @access public
	 * @var UserData[]
	 */
	public $submitterFullName;
	/**
	 * @access public
	 * @var string
	 */
	public $ticket;
	/**
	 * @access public
	 * @var integer
	 */
	public $wordCount;
	/**
	 * @access public
	 * @var Date
	 */
	public $workflowDueDate;
	/**
	 * @access public
	 * @var string
	 */
	public $workflowStatus;
}}

if (!class_exists("SubmissionSearchModelPagedList")) {
/**
 * SubmissionSearchModelPagedList
 */
class SubmissionSearchModelPagedList {
	/**
	 * @access public
	 * @var SimpleSubmissionSearchModel[]
	 */
	public $elements;
	/**
	 * @access public
	 * @var PagedListInfo
	 */
	public $pagedListInfo;
	/**
	 * @access public
	 * @var Task[]
	 */
	public $tasks;
	/**
	 * @access public
	 * @var integer
	 */
	public $totalCount;
}}

if (!class_exists("SubmissionSearchRequest")) {
/**
 * SubmissionSearchRequest
 */
class SubmissionSearchRequest {
	/**
	 * @access public
	 * @var ItemFolderEnum
	 */
	public $folder;
	/**
	 * @access public
	 * @var string[]
	 */
	public $projectTickets;
	/**
	 * @access public
	 * @var Date
	 */
	public $submissionDate;
	/**
	 * @access public
	 * @var Date
	 */
	public $submissionDueDate;
	/**
	 * @access public
	 * @var string
	 */
	public $submissionName;
}}

if (!class_exists("Target")) {
/**
 * Target
 */
class Target {
	/**
	 * @access public
	 * @var integer
	 */
	public $availableTasks;
	/**
	 * @access public
	 * @var Date
	 */
	public $dateCompleted;
	/**
	 * @access public
	 * @var Date
	 */
	public $dateCreated;
	/**
	 * @access public
	 * @var Date
	 */
	public $dateEstimated;
	/**
	 * @access public
	 * @var Document
	 */
	public $document;
	/**
	 * @access public
	 * @var Date
	 */
	public $downloadThresholdTimeStamp;
	/**
	 * @access public
	 * @var Date
	 */
	public $dueDate;
	/**
	 * @access public
	 * @var string
	 */
	public $fileName;
	/**
	 * @access public
	 * @var string
	 */
	public $id;
	/**
	 * @access public
	 * @var Phase[]
	 */
	public $phases;
	/**
	 * @access public
	 * @var Phase
	 */
	public $refPhase;
	/**
	 * @access public
	 * @var Language
	 */
	public $sourceLanguage;
	/**
	 * @access public
	 * @var integer
	 */
	public $sourceWordCount;
	/**
	 * @access public
	 * @var ItemStatusEnum
	 */
	public $status;
	/**
	 * @access public
	 * @var TargetInfo
	 */
	public $targetInfo;
	/**
	 * @access public
	 * @var Language
	 */
	public $targetLanguage;
	/**
	 * @access public
	 * @var integer
	 */
	public $targetWordCount;
	/**
	 * @access public
	 * @var string
	 */
	public $ticket;
	/**
	 * @access public
	 * @var TmStatistics
	 */
	public $tmStatistics;
	/**
	 * @access public
	 * @var WorkflowDefinition
	 */
	public $workflowDefinition;
}}

if (!class_exists("TargetInfo")) {
/**
 * TargetInfo
 */
class TargetInfo {
	/**
	 * @access public
	 * @var Date
	 */
	public $dateRequested;
	/**
	 * @access public
	 * @var string
	 */
	public $encoding;
	/**
	 * @access public
	 * @var string
	 */
	public $instructions;
	/**
	 * @access public
	 * @var Metadata[]
	 */
	public $metadata;
	/**
	 * @access public
	 * @var Priority
	 */
	public $priority;
	/**
	 * @access public
	 * @var integer
	 */
	public $requestedDueDate;
	/**
	 * @access public
	 * @var string
	 */
	public $targetLocale;
	/**
	 * @access public
	 * @var string
	 */
	public $workflowDefinitionTicket;
}}

if (!class_exists("TargetPagedList")) {
/**
 * TargetPagedList
 */
class TargetPagedList {
	/**
	 * @access public
	 * @var Target[]
	 */
	public $elements;
	/**
	 * @access public
	 * @var PagedListInfo
	 */
	public $pagedListInfo;
	/**
	 * @access public
	 * @var Task[]
	 */
	public $tasks;
	/**
	 * @access public
	 * @var integer
	 */
	public $totalCount;
}}

if (!class_exists("TargetSearchRequest")) {
/**
 * TargetSearchRequest
 */
class TargetSearchRequest {
	/**
	 * @access public
	 * @var Date
	 */
	public $dateCreated;
	/**
	 * @access public
	 * @var ItemFolderEnum
	 */
	public $folder;
	/**
	 * @access public
	 * @var string[]
	 */
	public $projectTickets;
	/**
	 * @access public
	 * @var string
	 */
	public $sourceLocaleId;
	/**
	 * @access public
	 * @var string
	 */
	public $submissionTicket;
	/**
	 * @access public
	 * @var string
	 */
	public $targetLocaleId;
}}

if (!class_exists("Task")) {
/**
 * Task
 */
class Task {
	/**
	 * @access public
	 * @var string
	 */
	public $groupName;
	/**
	 * @access public
	 * @var integer
	 */
	public $selectStyle;
	/**
	 * @access public
	 * @var integer
	 */
	public $taskId;
	/**
	 * @access public
	 * @var string
	 */
	public $taskName;
	/**
	 * @access public
	 * @var integer
	 */
	public $weight;
}}

if (!class_exists("TmStatistics")) {
/**
 * TmStatistics
 */
class TmStatistics {
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyRepetitionsWordCount1;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyRepetitionsWordCount10;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyRepetitionsWordCount2;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyRepetitionsWordCount3;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyRepetitionsWordCount4;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyRepetitionsWordCount5;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyRepetitionsWordCount6;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyRepetitionsWordCount7;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyRepetitionsWordCount8;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyRepetitionsWordCount9;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyWordCount1;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyWordCount10;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyWordCount2;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyWordCount3;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyWordCount4;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyWordCount5;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyWordCount6;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyWordCount7;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyWordCount8;
	/**
	 * @access public
	 * @var FuzzyTmStatistics
	 */
	public $fuzzyWordCount9;
	/**
	 * @access public
	 * @var integer
	 */
	public $inContextMatchWordCount;
	/**
	 * @access public
	 * @var integer
	 */
	public $noMatchWordCount;
	/**
	 * @access public
	 * @var integer
	 */
	public $oneHundredMatchWordCount;
	/**
	 * @access public
	 * @var integer
	 */
	public $repetitionWordCount;
	/**
	 * @access public
	 * @var integer
	 */
	public $totalWordCount;
}}

if (!class_exists("WorkflowDefinition")) {
/**
 * WorkflowDefinition
 */
class WorkflowDefinition {
	/**
	 * @access public
	 * @var string
	 */
	public $description;
	/**
	 * @access public
	 * @var string
	 */
	public $name;
	/**
	 * @access public
	 * @var string
	 */
	public $ticket;
	/**
	 * @access public
	 * @var EntityTypeEnum
	 */
	public $type;
}}

if (!class_exists("UserData")) {
/**
 * UserData
 */
class UserData {
	/**
	 * @access public
	 * @var string
	 */
	public $email;
	/**
	 * @access public
	 * @var string
	 */
	public $name;
}}

if (!class_exists("UserInfo")) {
/**
 * UserInfo
 */
class UserInfo {
	/**
	 * @access public
	 * @var boolean
	 */
	public $accountLocked;
	/**
	 * @access public
	 * @var boolean
	 */
	public $accountNonExpired;
	/**
	 * @access public
	 * @var string
	 */
	public $address;
	/**
	 * @access public
	 * @var boolean
	 */
	public $autoClaimMultipleTasks;
	/**
	 * @access public
	 * @var boolean
	 */
	public $claimMultipleJobTasks;
	/**
	 * @access public
	 * @var boolean
	 */
	public $credentialsNonExpired;
	/**
	 * @access public
	 * @var dateTime
	 */
	public $dateLastLogin;
	/**
	 * @access public
	 * @var string
	 */
	public $department;
	/**
	 * @access public
	 * @var string
	 */
	public $emailAddress;
	/**
	 * @access public
	 * @var boolean
	 */
	public $emailNotification;
	/**
	 * @access public
	 * @var boolean
	 */
	public $enabled;
	/**
	 * @access public
	 * @var string
	 */
	public $fax;
	/**
	 * @access public
	 * @var string
	 */
	public $firstName;
	/**
	 * @access public
	 * @var string
	 */
	public $lastName;
	/**
	 * @access public
	 * @var string
	 */
	public $password;
	/**
	 * @access public
	 * @var string
	 */
	public $phone1;
	/**
	 * @access public
	 * @var string
	 */
	public $phone2;
	/**
	 * @access public
	 * @var string
	 */
	public $timeZone;
	/**
	 * @access public
	 * @var string
	 */
	public $userName;
	/**
	 * @access public
	 * @var string
	 */
	public $userType;
}}

if (!class_exists("TiUserInfo")) {
/**
 * TiUserInfo
 */
class TiUserInfo {
	/**
	 * @access public
	 * @var LanguageDirection[]
	 */
	public $languageDirections;
	/**
	 * @access public
	 * @var integer
	 */
	public $organizationId;
	/**
	 * @access public
	 * @var string[]
	 */
	public $projectRoles;
	/**
	 * @access public
	 * @var string[]
	 */
	public $projectTicket;
	/**
	 * @access public
	 * @var string[]
	 */
	public $systemRoles;
	/**
	 * @access public
	 * @var integer
	 */
	public $vendorId;
}}

if (!class_exists("ClaimScopeEnum")) {
/**
 * ClaimScopeEnum
 */
class ClaimScopeEnum {
	/**
	 * @access public
	 * @var string
	 */
	public $name;
	/**
	 * @access public
	 * @var integer
	 */
	public $value;
}}

if (!class_exists("ProjectCustomFieldConfiguration")) {
/**
 * ProjectCustomFieldConfiguration
 */
class ProjectCustomFieldConfiguration {
	/**
	 * @access public
	 * @var string
	 */
	public $description;
	/**
	 * @access public
	 * @var boolean
	 */
	public $mandatory;
	/**
	 * @access public
	 * @var string
	 */
	public $name;
	/**
	 * @access public
	 * @var string
	 */
	public $type;
	/**
	 * @access public
	 * @var string
	 */
	public $values;
}}

if (!class_exists("SubmissionCustomFields")) {
/**
 * SubmissionCustomFields
 */
class SubmissionCustomFields {
	/**
	 * @access public
	 * @var string
	 */
	public $fieldName;
	/**
	 * @access public
	 * @var string
	 */
	public $fieldValue;
}}

if (!class_exists("UserProfile")) {
/**
 * UserProfile
 */
class UserProfile {
	/**
	 * @access public
	 * @var integer
	 */
	public $availableTasks;
	/**
	 * @access public
	 * @var string
	 */
	public $organizationName;
	/**
	 * @access public
	 * @var Role[]
	 */
	public $systemRoles;
	/**
	 * @access public
	 * @var Task[]
	 */
	public $tasks;
	/**
	 * @access public
	 * @var string
	 */
	public $ticket;
	/**
	 * @access public
	 * @var UserInfo
	 */
	public $userInfo;
	/**
	 * @access public
	 * @var string
	 */
	public $vendorName;
}}

if (!class_exists("Role")) {
/**
 * Role
 */
class Role {
	/**
	 * @access public
	 * @var Policy[]
	 */
	public $policies;
	/**
	 * @access public
	 * @var string
	 */
	public $roleId;
	/**
	 * @access public
	 * @var RoleTypeEnum
	 */
	public $roleType;
	/**
	 * @access public
	 * @var Task[]
	 */
	public $tasks;
	/**
	 * @access public
	 * @var string
	 */
	public $ticket;
}}

if (!class_exists("RoleTypeEnum")) {
/**
 * RoleTypeEnum
 */
class RoleTypeEnum {
	/**
	 * @access public
	 * @var integer
	 */
	public $value;
}}

if (!class_exists("Policy")) {
/**
 * Policy
 */
class Policy {
	/**
	 * @access public
	 * @var string
	 */
	public $category;
	/**
	 * @access public
	 * @var string
	 */
	public $policyId;
	/**
	 * @access public
	 * @var RoleTypeEnum
	 */
	public $policyType;
}}

if (!class_exists("LanguageWorkflowInfo")) {
/**
 * LanguageWorkflowInfo
 */
class LanguageWorkflowInfo {
	/**
	 * @access public
	 * @var Language
	 */
	public $sourceLanguage;
	/**
	 * @access public
	 * @var Language
	 */
	public $targetLanguage;
}}

if (!class_exists("BatchWorkflowInfo")) {
/**
 * BatchWorkflowInfo
 */
class BatchWorkflowInfo {
	/**
	 * @access public
	 * @var string
	 */
	public $batchName;
	/**
	 * @access public
	 * @var LanguageWorkflowInfo
	 */
	public $languageWorkflowInfo;
}}

if (!class_exists("TargetWorkflowInfo")) {
/**
 * TargetWorkflowInfo
 */
class TargetWorkflowInfo {
	/**
	 * @access public
	 * @var string
	 */
	public $batchName;
	/**
	 * @access public
	 * @var string
	 */
	public $documentName;
	/**
	 * @access public
	 * @var string
	 */
	public $fileName;
	/**
	 * @access public
	 * @var Language
	 */
	public $sourceLanguage;
	/**
	 * @access public
	 * @var Language
	 */
	public $targetLanguage;
	/**
	 * @access public
	 * @var string
	 */
	public $targetTicket;
}}

if (!class_exists("SubmissionWorkflowInfo")) {
/**
 * SubmissionWorkflowInfo
 */
class SubmissionWorkflowInfo {
	/**
	 * @access public
	 * @var BatchWorkflowInfo[]
	 */
	public $batchWorkflowInfos;
	/**
	 * @access public
	 * @var LanguageWorkflowInfo[]
	 */
	public $languageWorkflowInfos;
	/**
	 * @access public
	 * @var string
	 */
	public $phaseName;
	/**
	 * @access public
	 * @var integer
	 */
	public $submissionId;
	/**
	 * @access public
	 * @var string
	 */
	public $submissionName;
	/**
	 * @access public
	 * @var string
	 */
	public $submissionTicket;
	/**
	 * @access public
	 * @var TargetWorkflowInfo[]
	 */
	public $targetWorkflowInfos;
}}

if (!class_exists("WorkflowRequest")) {
/**
 * WorkflowRequest
 */
class WorkflowRequest {
	/**
	 * @access public
	 * @var BatchWorkflowInfo[]
	 */
	public $batchWorkflowInfos;
	/**
	 * @access public
	 * @var LanguageWorkflowInfo[]
	 */
	public $languageWorkflowInfos;
	/**
	 * @access public
	 * @var string
	 */
	public $phaseName;
	/**
	 * @access public
	 * @var string
	 */
	public $submissionTicket;
	/**
	 * @access public
	 * @var TargetWorkflowInfo[]
	 */
	public $targetWorkflowInfos;
}}

if (!class_exists("WorkflowRequestTicket")) {
/**
 * WorkflowRequestTicket
 */
class WorkflowRequestTicket {
	/**
	 * @access public
	 * @var string
	 */
	public $message;
	/**
	 * @access public
	 * @var string
	 */
	public $processTicket;
	/**
	 * @access public
	 * @var string
	 */
	public $submissionTicket;
}}

if (!class_exists("DownloadActionResult")) {
/**
 * DownloadActionResult
 */
class DownloadActionResult {
	/**
	 * @access public
	 * @var string
	 */
	public $message;
	/**
	 * @access public
	 * @var boolean
	 */
	public $processingFinished;
	/**
	 * @access public
	 * @var RepositoryItem
	 */
	public $repositoryItem;
}}

if (!class_exists("UploadActionResult")) {
/**
 * UploadActionResult
 */
class UploadActionResult {
	/**
	 * @access public
	 * @var string[]
	 */
	public $messages;
	/**
	 * @access public
	 * @var boolean
	 */
	public $processingFinished;
}}

if (!class_exists("DownloadCollateralResult")) {
/**
 * DownloadCollateralResult
 */
class DownloadCollateralResult {
	/**
	 * @access public
	 * @var string[]
	 */
	public $errorMessages;
	/**
	 * @access public
	 * @var boolean
	 */
	public $processingFinished;
	/**
	 * @access public
	 * @var RepositoryItem
	 */
	public $repositoryItem;
}}

if (!class_exists("contentType")) {
/**
 * contentType
 */
class contentType {
}}

if (!class_exists("checkDownloadAction")) {
/**
 * checkDownloadAction
 */
class checkDownloadAction {
	/**
	 * @access public
	 * @var WorkflowRequestTicket
	 */
	public $workflowRequestTicket;
}}

if (!class_exists("checkDownloadActionResponse")) {
/**
 * checkDownloadActionResponse
 */
class checkDownloadActionResponse {
	/**
	 * @access public
	 * @var DownloadActionResult
	 */
	public $return;
}}

if (!class_exists("checkUploadAction")) {
/**
 * checkUploadAction
 */
class checkUploadAction {
	/**
	 * @access public
	 * @var WorkflowRequestTicket
	 */
	public $workflowRequestTicket;
}}

if (!class_exists("checkUploadActionResponse")) {
/**
 * checkUploadActionResponse
 */
class checkUploadActionResponse {
	/**
	 * @access public
	 * @var UploadActionResult
	 */
	public $return;
}}

if (!class_exists("claim")) {
/**
 * claim
 */
class claim {
	/**
	 * @access public
	 * @var WorkflowRequest
	 */
	public $workflowRequest;
}}

if (!class_exists("claimResponse")) {
/**
 * claimResponse
 */
class claimResponse {
	/**
	 * @access public
	 * @var string
	 */
	public $return;
}}

if (!class_exists("download")) {
/**
 * download
 */
class download {
	/**
	 * @access public
	 * @var WorkflowRequest
	 */
	public $workflowRequest;
}}

if (!class_exists("downloadResponse")) {
/**
 * downloadResponse
 */
class downloadResponse {
	/**
	 * @access public
	 * @var WorkflowRequestTicket
	 */
	public $return;
}}

if (!class_exists("downloadPreview")) {
/**
 * downloadPreview
 */
class downloadPreview {
	/**
	 * @access public
	 * @var WorkflowRequest
	 */
	public $workflowRequest;
}}

if (!class_exists("downloadPreviewResponse")) {
/**
 * downloadPreviewResponse
 */
class downloadPreviewResponse {
	/**
	 * @access public
	 * @var WorkflowRequestTicket
	 */
	public $return;
}}

if (!class_exists("findAvailableWorkflowInfosForClaim")) {
/**
 * findAvailableWorkflowInfosForClaim
 */
class findAvailableWorkflowInfosForClaim {
	/**
	 * @access public
	 * @var integer
	 */
	public $limit;
}}

if (!class_exists("findAvailableWorkflowInfosForClaimResponse")) {
/**
 * findAvailableWorkflowInfosForClaimResponse
 */
class findAvailableWorkflowInfosForClaimResponse {
	/**
	 * @access public
	 * @var SubmissionWorkflowInfo[]
	 */
	public $return;
}}

if (!class_exists("findAvailableWorkflowInfosForDownload")) {
/**
 * findAvailableWorkflowInfosForDownload
 */
class findAvailableWorkflowInfosForDownload {
	/**
	 * @access public
	 * @var integer
	 */
	public $limit;
}}

if (!class_exists("findAvailableWorkflowInfosForDownloadResponse")) {
/**
 * findAvailableWorkflowInfosForDownloadResponse
 */
class findAvailableWorkflowInfosForDownloadResponse {
	/**
	 * @access public
	 * @var SubmissionWorkflowInfo[]
	 */
	public $return;
}}

if (!class_exists("findAvailableWorkflowInfosForDownloadBySubmissionTickets")) {
/**
 * findAvailableWorkflowInfosForDownloadBySubmissionTickets
 */
class findAvailableWorkflowInfosForDownloadBySubmissionTickets {
	/**
	 * @access public
	 * @var string[]
	 */
	public $submissionTickets;
}}

if (!class_exists("findAvailableWorkflowInfosForDownloadBySubmissionTicketsResponse")) {
/**
 * findAvailableWorkflowInfosForDownloadBySubmissionTicketsResponse
 */
class findAvailableWorkflowInfosForDownloadBySubmissionTicketsResponse {
	/**
	 * @access public
	 * @var SubmissionWorkflowInfo[]
	 */
	public $return;
}}

if (!class_exists("upload")) {
/**
 * upload
 */
class upload {
	/**
	 * @access public
	 * @var ResourceInfo
	 */
	public $resourceInfo;
	/**
	 * @access public
	 * @var base64Binary
	 */
	public $data;
}}

if (!class_exists("uploadResponse")) {
/**
 * uploadResponse
 */
class uploadResponse {
	/**
	 * @access public
	 * @var WorkflowRequestTicket
	 */
	public $return;
}}

if (!class_exists("WorkflowService_4180")) {
/**
 * WorkflowService_4180
 * @author WSDLInterpreter
 */
class WorkflowService_4180 extends SoapClient {
	/**
	 * Default class map for wsdl=>php
	 * @access private
	 * @var array
	 */
	private static $classmap = array(
		"Notification" => "Notification",
		"NotificationPriority" => "NotificationPriority",
		"Announcement" => "Announcement",
		"Batch" => "Batch",
		"ContentMonitorPluginInfo" => "ContentMonitorPluginInfo",
		"Date" => "Date",
		"Document" => "Document",
		"DocumentGroup" => "DocumentGroup",
		"DocumentInfo" => "DocumentInfo",
		"DocumentPagedList" => "DocumentPagedList",
		"DocumentSearchRequest" => "DocumentSearchRequest",
		"DocumentTicket" => "DocumentTicket",
		"EntityTypeEnum" => "EntityTypeEnum",
		"FileFormatProfile" => "FileFormatProfile",
		"FileFormatProgressData" => "FileFormatProgressData",
		"FileProgressData" => "FileProgressData",
		"FuzzyTmStatistics" => "FuzzyTmStatistics",
		"ItemFolderEnum" => "ItemFolderEnum",
		"ItemStatusEnum" => "ItemStatusEnum",
		"Metadata" => "Metadata",
		"Language" => "Language",
		"LanguageDirection" => "LanguageDirection",
		"LanguageDirectionModel" => "LanguageDirectionModel",
		"LanguagePhaseInfo" => "LanguagePhaseInfo",
		"Organization" => "Organization",
		"OrganizationInfo" => "OrganizationInfo",
		"PagedListInfo" => "PagedListInfo",
		"Phase" => "Phase",
		"PreviewResult" => "PreviewResult",
		"Priority" => "Priority",
		"Project" => "Project",
		"ProjectInfo" => "ProjectInfo",
		"ProjectLanguage" => "ProjectLanguage",
		"ProjectLanguageDirection" => "ProjectLanguageDirection",
		"ProjectAClient" => "ProjectAClient",
		"RepositoryItem" => "RepositoryItem",
		"ResourceInfo" => "ResourceInfo",
		"ResourceType" => "ResourceType",
		"Submission" => "Submission",
		"SubmissionInfo" => "SubmissionInfo",
		"SubmissionPagedList" => "SubmissionPagedList",
		"SimpleSubmissionSearchModel" => "SimpleSubmissionSearchModel",
		"SubmissionSearchModelPagedList" => "SubmissionSearchModelPagedList",
		"SubmissionSearchRequest" => "SubmissionSearchRequest",
		"Target" => "Target",
		"TargetInfo" => "TargetInfo",
		"TargetPagedList" => "TargetPagedList",
		"TargetSearchRequest" => "TargetSearchRequest",
		"Task" => "Task",
		"TmStatistics" => "TmStatistics",
		"WorkflowDefinition" => "WorkflowDefinition",
		"UserData" => "UserData",
		"UserInfo" => "UserInfo",
		"TiUserInfo" => "TiUserInfo",
		"ClaimScopeEnum" => "ClaimScopeEnum",
		"ProjectCustomFieldConfiguration" => "ProjectCustomFieldConfiguration",
		"SubmissionCustomFields" => "SubmissionCustomFields",
		"UserProfile" => "UserProfile",
		"Role" => "Role",
		"RoleTypeEnum" => "RoleTypeEnum",
		"Policy" => "Policy",
		"LanguageWorkflowInfo" => "LanguageWorkflowInfo",
		"BatchWorkflowInfo" => "BatchWorkflowInfo",
		"TargetWorkflowInfo" => "TargetWorkflowInfo",
		"SubmissionWorkflowInfo" => "SubmissionWorkflowInfo",
		"WorkflowRequest" => "WorkflowRequest",
		"WorkflowRequestTicket" => "WorkflowRequestTicket",
		"DownloadActionResult" => "DownloadActionResult",
		"UploadActionResult" => "UploadActionResult",
		"DownloadCollateralResult" => "DownloadCollateralResult",
		"contentType" => "contentType",
		"base64Binary" => "base64Binary",
		"hexBinary" => "hexBinary",
		"checkDownloadAction" => "checkDownloadAction",
		"checkDownloadActionResponse" => "checkDownloadActionResponse",
		"checkUploadAction" => "checkUploadAction",
		"checkUploadActionResponse" => "checkUploadActionResponse",
		"claim" => "claim",
		"claimResponse" => "claimResponse",
		"download" => "download",
		"downloadResponse" => "downloadResponse",
		"downloadPreview" => "downloadPreview",
		"downloadPreviewResponse" => "downloadPreviewResponse",
		"findAvailableWorkflowInfosForClaim" => "findAvailableWorkflowInfosForClaim",
		"findAvailableWorkflowInfosForClaimResponse" => "findAvailableWorkflowInfosForClaimResponse",
		"findAvailableWorkflowInfosForDownload" => "findAvailableWorkflowInfosForDownload",
		"findAvailableWorkflowInfosForDownloadResponse" => "findAvailableWorkflowInfosForDownloadResponse",
		"findAvailableWorkflowInfosForDownloadBySubmissionTickets" => "findAvailableWorkflowInfosForDownloadBySubmissionTickets",
		"findAvailableWorkflowInfosForDownloadBySubmissionTicketsResponse" => "findAvailableWorkflowInfosForDownloadBySubmissionTicketsResponse",
		"upload" => "upload",
		"uploadResponse" => "uploadResponse",
	);

	/**
	 * Constructor using wsdl location and options array
	 * @param string $wsdl WSDL location for this service
	 * @param array $options Options for the SoapClient
	 */
	public function __construct($wsdl="https://gl-connect2.translations.com/PD/services/WorkflowService_4180.wsdl", $options=array(), $headers = NULL) {
		foreach(self::$classmap as $wsdlClassName => $phpClassName) {
		    if(!isset($options['classmap'][$wsdlClassName])) {
		        $options['classmap'][$wsdlClassName] = $phpClassName;
		    }
		}
		parent::__construct($wsdl, $options);
		parent::__setSoapHeaders ( $headers );
	}

	/**
	 * Checks if an argument list matches against a valid argument type list
	 * @param array $arguments The argument list to check
	 * @param array $validParameters A list of valid argument types
	 * @return boolean true if arguments match against validParameters
	 * @throws Exception invalid function signature message
	 */
	public function _checkArguments($arguments, $validParameters) {
		$variables = "";
		foreach ($arguments as $arg) {
		    $type = gettype($arg);
		    if ($type == "object") {
		        $type = get_class($arg);
		    }
		    $variables .= "(".$type.")";
		}
		if (!in_array($variables, $validParameters)) {
		    throw new Exception("Invalid parameter types: ".str_replace(")(", ", ", $variables));
		}
		return true;
	}

	/**
	 * Service Call: checkDownloadAction
	 * Parameter options:
	 * (checkDownloadAction) parameters
	 * (checkDownloadAction) parameters
	 * (checkDownloadAction) parameters
	 * @param mixed,... See function description for parameter options
	 * @return checkDownloadActionResponse
	 * @throws Exception invalid function signature message
	 */
	public function checkDownloadAction($mixed = null) {
		$validParameters = array(
			"(checkDownloadAction)",
			"(checkDownloadAction)",
			"(checkDownloadAction)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("checkDownloadAction", $args);
	}


	/**
	 * Service Call: checkUploadAction
	 * Parameter options:
	 * (checkUploadAction) parameters
	 * (checkUploadAction) parameters
	 * (checkUploadAction) parameters
	 * @param mixed,... See function description for parameter options
	 * @return checkUploadActionResponse
	 * @throws Exception invalid function signature message
	 */
	public function checkUploadAction($mixed = null) {
		$validParameters = array(
			"(checkUploadAction)",
			"(checkUploadAction)",
			"(checkUploadAction)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("checkUploadAction", $args);
	}


	/**
	 * Service Call: claim
	 * Parameter options:
	 * (claim) parameters
	 * (claim) parameters
	 * (claim) parameters
	 * @param mixed,... See function description for parameter options
	 * @return claimResponse
	 * @throws Exception invalid function signature message
	 */
	public function claim($mixed = null) {
		$validParameters = array(
			"(claim)",
			"(claim)",
			"(claim)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("claim", $args);
	}


	/**
	 * Service Call: download
	 * Parameter options:
	 * (download) parameters
	 * (download) parameters
	 * (download) parameters
	 * @param mixed,... See function description for parameter options
	 * @return downloadResponse
	 * @throws Exception invalid function signature message
	 */
	public function download($mixed = null) {
		$validParameters = array(
			"(download)",
			"(download)",
			"(download)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("download", $args);
	}


	/**
	 * Service Call: downloadPreview
	 * Parameter options:
	 * (downloadPreview) parameters
	 * (downloadPreview) parameters
	 * (downloadPreview) parameters
	 * @param mixed,... See function description for parameter options
	 * @return downloadPreviewResponse
	 * @throws Exception invalid function signature message
	 */
	public function downloadPreview($mixed = null) {
		$validParameters = array(
			"(downloadPreview)",
			"(downloadPreview)",
			"(downloadPreview)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("downloadPreview", $args);
	}


	/**
	 * Service Call: findAvailableWorkflowInfosForClaim
	 * Parameter options:
	 * (findAvailableWorkflowInfosForClaim) parameters
	 * (findAvailableWorkflowInfosForClaim) parameters
	 * (findAvailableWorkflowInfosForClaim) parameters
	 * @param mixed,... See function description for parameter options
	 * @return findAvailableWorkflowInfosForClaimResponse
	 * @throws Exception invalid function signature message
	 */
	public function findAvailableWorkflowInfosForClaim($mixed = null) {
		$validParameters = array(
			"(findAvailableWorkflowInfosForClaim)",
			"(findAvailableWorkflowInfosForClaim)",
			"(findAvailableWorkflowInfosForClaim)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("findAvailableWorkflowInfosForClaim", $args);
	}


	/**
	 * Service Call: findAvailableWorkflowInfosForDownload
	 * Parameter options:
	 * (findAvailableWorkflowInfosForDownload) parameters
	 * (findAvailableWorkflowInfosForDownload) parameters
	 * (findAvailableWorkflowInfosForDownload) parameters
	 * @param mixed,... See function description for parameter options
	 * @return findAvailableWorkflowInfosForDownloadResponse
	 * @throws Exception invalid function signature message
	 */
	public function findAvailableWorkflowInfosForDownload($mixed = null) {
		$validParameters = array(
			"(findAvailableWorkflowInfosForDownload)",
			"(findAvailableWorkflowInfosForDownload)",
			"(findAvailableWorkflowInfosForDownload)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("findAvailableWorkflowInfosForDownload", $args);
	}


	/**
	 * Service Call: findAvailableWorkflowInfosForDownloadBySubmissionTickets
	 * Parameter options:
	 * (findAvailableWorkflowInfosForDownloadBySubmissionTickets) parameters
	 * (findAvailableWorkflowInfosForDownloadBySubmissionTickets) parameters
	 * (findAvailableWorkflowInfosForDownloadBySubmissionTickets) parameters
	 * @param mixed,... See function description for parameter options
	 * @return findAvailableWorkflowInfosForDownloadBySubmissionTicketsResponse
	 * @throws Exception invalid function signature message
	 */
	public function findAvailableWorkflowInfosForDownloadBySubmissionTickets($mixed = null) {
		$validParameters = array(
			"(findAvailableWorkflowInfosForDownloadBySubmissionTickets)",
			"(findAvailableWorkflowInfosForDownloadBySubmissionTickets)",
			"(findAvailableWorkflowInfosForDownloadBySubmissionTickets)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("findAvailableWorkflowInfosForDownloadBySubmissionTickets", $args);
	}


	/**
	 * Service Call: upload
	 * Parameter options:
	 * (upload) parameters
	 * (upload) parameters
	 * (upload) parameters
	 * @param mixed,... See function description for parameter options
	 * @return uploadResponse
	 * @throws Exception invalid function signature message
	 */
	public function upload($mixed = null) {
		$validParameters = array(
			"(upload)",
			"(upload)",
			"(upload)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("upload", $args);
	}


}}

?>