<?php
/**
 * CDNPAL NGINAD Project
 *
 * @link http://www.nginad.com
 * @copyright Copyright (c) 2013-2015 CDNPAL Ltd. All Rights Reserved
 * @license GPLv3
 */
namespace DashboardManager\Controller;

use DashboardManager\ParentControllers\PublisherAbstractActionController;
use Zend\View\Model\ViewModel;
use transformation;

/**
 * @author Kelvin Mok
 * This is the Ad spaces Controller class that controls the management
 * of ad space management functions.
 */
class ZoneController extends PublisherAbstractActionController {
    
    protected $ad_template_data; 
    
    /**
     * Query for initial domain data necessary to obtain the associated object information, and
     * to verify that the Domain associated with the ad space is valid.
     * 
     * @param integer $DomainID An integer of the domain ID associated with the ad spaces.
     * @param integer $DomainOwnerID The domain owner ID associated with the domain ID.
     * @throws \InvalidArgumentException will be thrown when an integer is not supplied to the function parameters.
     * @throws Exception will be thrown when there is a database error while the module is in Debug mode.
     * @return NULL|\DashboardManager\model\PublisherWebsite Returns a NULL when no matching domains are found. Otherwise, the domain object is returned.
     */
    protected function get_domain_data($DomainID, $DomainOwnerID)
    {
        if (!is_int($DomainID) || !is_int($DomainOwnerID)):
        
            throw new \InvalidArgumentException(
                "ZoneController class, get_domain_data function expects an integer for \$DomainID and \$DomainOwnerID;" .
                " However, type " . gettype($DomainID) . " and type " . gettype($DomainOwnerID) . " was provided instead."
            );
        endif;

        //Initialize and define variables.
        $PublisherWebsiteFactory = \_factory\PublisherWebsite::get_instance();
        $PublisherWebsiteListObj = new \model\PublisherWebsite;
        $parameters = array(); // Set the parameters to empty first.
        
        //Populate parameters.
        $parameters['DomainOwnerID'] = $DomainOwnerID;
        $parameters['PublisherWebsiteID'] = $DomainID;

        // Pull website information.
        try {
        $PublisherWebsiteListObj = $PublisherWebsiteFactory->get_row_object($parameters);
        }
        catch(\Exception $e)
        {
            // If there is a DB error, return an empty object as if no rows were found!
            if ($this->debug):
            
                throw $e;
            endif;
            return null;
        }
        
        if (intval($PublisherWebsiteListObj->PublisherWebsiteID) > 0):
        
            return $PublisherWebsiteListObj;
        endif;
        
        return null;
        
    }
    
    /**
     * Obtain an array of ad templates available from the database.
     * @return array An array of ad templates available, ID as the key and TemplateName as the value.
     */
    protected function get_ad_templates()
    {
        $AdTemplatesFactory = \_factory\AdTemplates::get_instance();
        $AdTemplatesObjList = array();
        $AdTemplatesParameters = array();
        $AdTemplateList = array('' => 'CUSTOM');
        
        $AdTemplatesObjList = $AdTemplatesFactory->get_object($AdTemplatesParameters);
        
        foreach ($AdTemplatesObjList as $TemplateItem):
        
        	$AdTemplateList[$TemplateItem->AdTemplateID] = $TemplateItem->TemplateName . ' (' . $TemplateItem->Width . ' x ' . $TemplateItem->Height . ')';
        endforeach;
        
        return $AdTemplateList;
        
    }
    
    /**
     * Display the Ad Zone available to act upon.
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->initialize();
        
        $error_message = null;
        $ZoneList = array();
        $DomainID = intval($this->params()->fromRoute('param1', 0));
        
        $DomainObj = $this->get_domain_data($DomainID, $this->PublisherInfoID);
        
        if ($DomainObj === null):
        
            $error_message = "An invalid publishing web domain was specified for the specified user.";
            
        
        else: 
        
            $PublisherAdZoneFactory = \_factory\PublisherAdZone::get_instance();
            $parameters = array();
            
            // You must check both the DomainOwnerID to make sure
            // that the user does indeed own the entry; otherwise we have a security
            // problem. You also need to specify the PublisherAdZone for the PublisherWebsiteID, since both
            // Websites and Ads tables have PublisherWebsiteID as a column.
            $parameters["PublisherAdZone.PublisherWebsiteID"] = $DomainID;
            $parameters["PublisherWebsite.DomainOwnerID"] = $this->PublisherInfoID;
            
            try {
                $ZoneList = $PublisherAdZoneFactory->get_joined($parameters);
            }
            catch(\Exception $e)
            {
                $error_message = "A database error has occurred: " . $e->getMessage();
                $ZoneList = array();
            }
        endif;
        if ($this->is_admin):
        
            $headers = array("#","Ad Zone Name","Status","Space Size","Floor Price","Total Ask","Impressions","Total Revenue","Created","Updated","Action");
            $meta_data = array("AdName","AdStatus","AutoApprove","AdTemplateID","FloorPrice","TotalAsk","TotalImpressions","TotalAmount","DateCreated","DateUpdated");
        
        else:
        
            $headers = array("#","Ad Zone Name","Status","Space Size","Floor Price","Total Ask","Impressions","Total Revenue","Created","Updated","Action");
            $meta_data = array("AdName","AdStatus","AutoApprove","AdTemplateID","FloorPrice","TotalAsk","TotalImpressions","TotalAmount","DateCreated","DateUpdated");
        endif;
        
        // TO DO: Permission issues.
        
        $view = new ViewModel(array(
        	'true_user_name' => $this->true_user_name,
            'zone_list_raw' => $ZoneList,
            'zone_list' => $this->order_data_table($meta_data,$ZoneList,$headers),
            'is_admin' => $this->is_admin,
            'user_id_list' => $this->user_id_list_publisher,
            'impersonate_id' => $this->ImpersonateID,
            'effective_id' => $this->EffectiveID,
            'domain_obj' => $DomainObj,
            'error_message' => $error_message,
        	'dashboard_view' => 'publisher',
	    	'user_identity' => $this->identity()
        ));
        
        return $view;
    }
    
    /**
     * Create a new ad space.
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:Ambigous <NULL, string>  Ambigous <NULL, \DashboardManager\model\PublisherWebsite>
     */
    public function createAction()
    {
        $this->initialize();
        
        $error_message = null;

        $DomainID = intval($this->params()->fromRoute('param1', 0));
        
        $DomainObj = $this->get_domain_data($DomainID, $this->PublisherInfoID);
        
        if ($DomainObj === null):
        
        	$error_message = "An invalid publishing web domain was specified for the specified user.";
            
        elseif ($DomainObj->ApprovalFlag == 2):

        	$error_message = "This domain was rejected and you can not add any new zones.";
        
        endif;
        
        $needed_input = array(
				'AdName',
				'Description',
				'PassbackAdTag',
				'FloorPrice',
				'Width',
				'Height'
		);
		
        $AdTemplateList = $this->get_ad_templates();
        
        $request = $this->getRequest();
 
        
        if ($request->isPost() && $DomainObj !== null && $error_message === null):
        
            $ad = new \model\PublisherAdZone();
            
            $validate = $this->validateInput($needed_input, false);
            
            if ($validate):
            
                $PublisherAdZoneFactory = \_factory\PublisherAdZone::get_instance();
                
                $ad->AdName = $request->getPost("AdName");
				$ad->Description = $request->getPost("Description");
				$ad->PassbackAdTag = $request->getPost("PassbackAdTag");
				$ad->FloorPrice = $request->getPost("FloorPrice");
				$ad->AdTemplateID = $request->getPost("AdTemplateID");
				$ad->IsMobileFlag = $request->getPost("IsMobileFlag");
				$ad->Width = $request->getPost("Width");
				$ad->Height = $request->getPost("Height");
                
                // Check to see if an entry exists with the same name for the same website domain. A NULL means there are no duplicates.
                if ($PublisherAdZoneFactory->get_row(array("PublisherWebsiteID" => $DomainObj->PublisherWebsiteID, "AdName" => $ad->AdName)) === null):
                
                    $ad->PublisherWebsiteID = $DomainObj->PublisherWebsiteID;
                          
                    if($ad->AdTemplateID != null) {
						$AdTemplatesFactory = \_factory\AdTemplates::get_instance();
						$AdTemplatesObj = $AdTemplatesFactory->get_row(array("AdTemplateID" => $ad->AdTemplateID));
						$ad->Width = $AdTemplatesObj->Width;
						$ad->Height = $AdTemplatesObj->Height;
					}
                    
                    
                    try {
                    $PublisherAdZoneFactory->save_ads($ad);
                    return $this->redirect()->toRoute('publisher/zone',array('param1' => $DomainObj->PublisherWebsiteID));
                    }
                    catch(\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
                        $error_message ="ERROR " . $e->getCode().  ": A database error has occurred, please contact customer service.";
                        $error_message .= "Details: " . $e->getMessage();
                    }
                
                else: 
                
                    $error_message = "ERROR: An ad with the name \"" . $ad->AdName . "\" already exists for the domain \"" . $DomainObj->WebDomain . "\"."; 
                endif;
            
            else:
            
                $error_message = "ERROR: Required fields are not filled in or invalid input.";
            endif;
            
        
        else:
        
             // If first coming to this form, set the Ad Owner ID.
        endif;
        
        return array(
        		'error_message' => $error_message,
        		'is_admin' => $this->is_admin,
        		'user_id_list' => $this->user_id_list_publisher,
        		'effective_id' => $this->EffectiveID,
        		'impersonate_id' => $this->ImpersonateID,
                'domain_obj' => $DomainObj,
        		'publisheradzonetype_options'  => $this->getPublisherAdZoneTypeOptions(),
        		'true_user_name' => $this->true_user_name,
        		'dashboard_view' => 'publisher',
        		'AdOwnerID' => $this->PublisherInfoID,
        		'AdTemplateList' => $this->get_ad_templates(),
	    		'user_identity' => $this->identity()
        );
    }
    
    /**
     * Edit existing ad space.
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:Ambigous <NULL, string>  Ambigous <NULL, \DashboardManager\model\PublisherWebsite> Ambigous <number, NULL>
     */
    public function editAction()
    {
        $this->initialize();
        $error_message = null;
        
        $DomainID = intval($this->params()->fromRoute('param1', 0));
        $PublisherAdZoneFactory = \_factory\PublisherAdZone::get_instance();
        $AdCampaignBannerFactory = \_factory\AdCampaignBanner::get_instance();
		
        $current_publisheradzonetype = AD_TYPE_ANY_REMNANT;
        
        $editResultObj = new \model\PublisherAdZone();
        
        $DomainObj = $this->get_domain_data($DomainID, $this->PublisherInfoID);
        
        if ($DomainObj === null):
        
        	$error_message = "An invalid publishing web domain was specified for the specified user.";
        
        elseif ($DomainObj->ApprovalFlag == 2):
        
        	$error_message = "This domain was rejected and you can not edit this zone.";
        
        else:
        
            $needed_input = array(
            	'PublisherAdZoneTypeID',
				'AdName',
				'Description',
				'PassbackAdTag',
				'FloorPrice',
				'Width',
				'Height'
			);
		
            $publisher_ad_zone_type_id = AD_TYPE_ANY_REMNANT;
            
            $AdTemplateList = $this->get_ad_templates();
            $request = $this->getRequest();
            
            // Make sure the value provided is valid.
            $AdSpaceID = intval($this->params()->fromRoute('id', 0));

            if ($AdSpaceID > 0):
            
                $AdSpaceParameters = array("PublisherWebsiteID" => $DomainObj->PublisherWebsiteID, "PublisherAdZoneID" => $AdSpaceID);
                $editResultObj = $PublisherAdZoneFactory->get_row_object($AdSpaceParameters);

                if (intval($editResultObj->PublisherAdZoneID) == $AdSpaceID && intval($editResultObj->PublisherWebsiteID) == $DomainObj->PublisherWebsiteID):
                     
                	$current_publisheradzonetype   = $editResultObj->PublisherAdZoneTypeID;
                
                    if ($request->isPost()):
                
                    	$validate = $this->validateInput($needed_input, false);
            
            			if ($validate):
            			
            				$publisher_ad_zone_type_id 				= $request->getPost("PublisherAdZoneTypeID");

            				$editResultObj->PublisherAdZoneTypeID	= $publisher_ad_zone_type_id;
	                    	$editResultObj->AdName 					= $request->getPost("AdName");
							$editResultObj->Description 			= $request->getPost("Description");
							$editResultObj->PassbackAdTag 			= $request->getPost("PassbackAdTag");
							$editResultObj->FloorPrice 				= $request->getPost("FloorPrice");
							$editResultObj->AdTemplateID 			= $request->getPost("AdTemplateID");
							$editResultObj->IsMobileFlag 			= $request->getPost("IsMobileFlag");
							$editResultObj->Width 					= $request->getPost("Width");
							$editResultObj->Height 					= $request->getPost("Height");
                    	
							$linkedbanners 							= $this->getRequest()->getPost('linkedbanners');
							
							$auto_approve_zones = $this->config_handle['settings']['publisher']['auto_approve_zones'];
							$editResultObj->AutoApprove = ($auto_approve_zones == true) ? 1 : 0;
							
                    	    // Disapprove the changes if not admin.
                            if ($this->is_admin || $auto_approve_zones == true):
                                $editResultObj->AdStatus = 1;
                            else:
                            	$editResultObj->AdStatus = 0;
                            endif;
                    	    
                			$editResultObj->PublisherWebsiteID = $DomainObj->PublisherWebsiteID;
                			if($editResultObj->AdTemplateID != null) {
								$AdTemplatesFactory = \_factory\AdTemplates::get_instance();
								$AdTemplatesObj = $AdTemplatesFactory->get_row(array("AdTemplateID" => $editResultObj->AdTemplateID));
								$editResultObj->Width = $AdTemplatesObj->Width;
								$editResultObj->Height = $AdTemplatesObj->Height;
							}
                			
                			
                			try {
                				$PublisherAdZoneFactory->save_ads($editResultObj);
                				
                				$LinkedBannerToAdZoneFactory = \_factory\LinkedBannerToAdZone::get_instance();
                				$LinkedBannerToAdZoneFactory->deleteLinkedBannerToAdZoneByPublisherAdZoneID($editResultObj->PublisherAdZoneID);

                				// campaigntype AD_TYPE_CONTRACT case
                				if ($publisher_ad_zone_type_id == AD_TYPE_CONTRACT && $linkedbanners != null && count($linkedbanners) > 0):
                				
	                				foreach($linkedbanners as $linked_banner_id):
	                					
	                					$params = array();
	                					$params["AdCampaignBannerID"] = $linked_banner_id;
	                					$LinkedAdCampaignBanner = $AdCampaignBannerFactory->get_row($params);
	                					
	                					if ($LinkedAdCampaignBanner == null):
	                						continue;
	                					endif;
	                				
		                				$LinkedBannerToAdZone = new \model\LinkedBannerToAdZone();
		                				$LinkedBannerToAdZone->AdCampaignBannerID 			= intval($linked_banner_id);
		                				$LinkedBannerToAdZone->PublisherAdZoneID			= $editResultObj->PublisherAdZoneID;
		                				$LinkedBannerToAdZone->Weight						= intval($LinkedAdCampaignBanner->Weight);
		                				$LinkedBannerToAdZone->DateCreated					= date("Y-m-d H:i:s");
		                				$LinkedBannerToAdZone->DateUpdated					= date("Y-m-d H:i:s");
		                				$LinkedBannerToAdZoneFactory->saveLinkedBannerToAdZone($LinkedBannerToAdZone);
		                				
		                				$AdCampaignBannerFactory->updateAdCampaignBannerAdCampaignType($LinkedAdCampaignBanner->AdCampaignBannerID, AD_TYPE_CONTRACT);
		                				
	                				endforeach;
                				
                				endif;
                				
                				return $this->redirect()->toRoute('publisher/zone',array('param1' => $DomainObj->PublisherWebsiteID));
                			}
                			catch(\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
                				$error_message ="ERROR " . $e->getCode().  ": A database error has occurred, please contact customer service.";
                				$error_message .= "Details: " . $e->getMessage();
                			}
                    	
                    	else:
                    	
                    		$error_message = "ERROR: Required fields are not filled in or invalid input.";
                    	endif;
                    
                    
                    else:
                    
                        //OK Display edit.
                    endif;
                    
                
                else:
                
                    $error_message = "An invalid Ad Zone ID was provided.";
                endif;
                
            
            else: 
            
                $error_message = "An invalid Ad Zone ID was provided.";
            endif;
        endif;
        return array(
        		'error_message' => $error_message,
        		'is_admin' => $this->is_admin,
        		'user_id_list' => $this->user_id_list_publisher,
        		'effective_id' => $this->EffectiveID,
        		'impersonate_id' => $this->ImpersonateID,
        		'domain_obj' => $DomainObj,
        		'current_publisheradzonetype'  => $current_publisheradzonetype,
        		'publisheradzonetype_options'  => $this->getPublisherAdZoneTypeOptions(),
        		'editResultObj' => $editResultObj,
        		'AdTemplateList' => $this->get_ad_templates(),
        		'true_user_name' => $this->true_user_name,
        		'dashboard_view' => 'publisher',
	    		'user_identity' => $this->identity()
        );
    }
    
    public function deleteAction()
    {
        $this->initialize();
        $error_message = null;
        $DomainID = intval($this->params()->fromRoute('param1', 0));
        $PublisherAdZoneFactory = \_factory\PublisherAdZone::get_instance();
        
        $DomainObj = $this->get_domain_data($DomainID, $this->PublisherInfoID);
        $success = false;
        
        if ($DomainObj === null):
        
        	$error_message = "An invalid publishing web domain was specified for the specified user.";
        
        
        else:
        
            $AdTemplateList = $this->get_ad_templates();
            $request = $this->getRequest();
            
            // Make sure the value provided is valid.
            $AdSpaceID = intval($this->params()->fromRoute('id', 0));
            
            if ($AdSpaceID > 0):
            
            	$AdSpaceParameters = array("PublisherWebsiteID" => $DomainObj->PublisherWebsiteID, "PublisherAdZoneID" => $AdSpaceID);
            	$deleteCheckResultObj = $PublisherAdZoneFactory->get_row_object($AdSpaceParameters);
            	
	           	//if (intval($deleteCheckResultObj->PublisherAdZoneID) == $AdSpaceID && intval($deleteCheckResultObj->PublisherWebsiteID) == $DomainObj->PublisherWebsiteID):
 
            		if ($request->isPost()): 
            		
            		    if ($request->getPost('del', 'No') == 'Yes'):
            		    
            		    	// Is this user allowed to delete this entry?
            		    	if ($this->is_admin || $DomainObj->DomainOwnerID == $this->PublisherInfoID):
            		    	
            		    		if (intval($PublisherAdZoneFactory->delete_zone(intval($deleteCheckResultObj->PublisherAdZoneID))) > -1):
            		    		
            		    			// Delete success! Return to publisher.
            		    			$success = true;
            		    			            		    		
            		    		else:
            		    		
            		    			// Something blew up.
            		    			$error_message = "Unable to delete the entry. Please contact customer service.";
            		    		endif;
            		    	
            		    	else:
            		    	
            		    		// User is either not the owner of the entry, or is not an admin.
            		    		$error_message = "You do not have permission to delete this entry.";
            		    	endif;
            		    
            		    else:
          		    
            		    	// Cancel.
            		    endif;
            
            		
            		else:
            		
            			//OK Display edit.
            		endif;
            
            	
            	//else:
            	
            		//$error_message = "An invalid Ad Zone ID was provided.";
            	//endif;
            
            
            else:
            
            	$error_message = "An invalid Ad Zone ID was provided.";
            endif;
        endif;
        
         $data = array(
	        'success' => $success,
	        'data' => array('error_msg' => $error_message)
   		 );
   		 
         return $this->getResponse()->setContent(json_encode($data));
        
    }
    
    /**
     * Toggle the approval given the supplied flag to toggle.
     *
     * @param integer $flag 0 = Pending | 1 = Approved
     * @return boolean TRUE if successful, FALSE if failure.
     */
    private function adApprovalToggle($flag)
    {
        $DomainID = intval($this->params()->fromRoute('param1', 0));
        $PublisherAdZoneID = intval($this->params()->fromRoute('id',0));
        
        if ($this->is_admin && $DomainID > 0 && $PublisherAdZoneID > 0 && ($flag === 0 || $flag === 1 || $flag === 2)):
        
            $DomainObj = $this->get_domain_data($DomainID, $this->PublisherInfoID);
            
            if ($DomainObj === null):
            
            	$error_message = "An invalid publishing web domain was specified for the specified user.";
            
            else: 
            
                $PublisherAdZoneFactory = \_factory\PublisherAdZone::get_instance();
                $AdObject = new \model\PublisherAdZone();
                $parameters = array("PublisherWebsiteID" => $DomainObj->PublisherWebsiteID, "PublisherAdZoneID" => $PublisherAdZoneID);
                $AdObject = $PublisherAdZoneFactory->get_row_object($parameters);
                
                if (intval($AdObject->PublisherAdZoneID) == $PublisherAdZoneID):
                	
                	$AdObject->AutoApprove = 0;

                    $AdObject->AdStatus = intval($flag);
                    if ($PublisherAdZoneFactory->save_ads($AdObject)):
                    
                        
                        return TRUE;
                    endif;
                endif;
            endif;
        endif;
        
        return FALSE;
        
    }
    
    /**
     * Approve an Ad space.
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function approveAction()
    {
        $this->initialize();
        $DomainID = intval($this->params()->fromRoute('param1', 0));
        $this->adApprovalToggle(1);
        return $this->redirect()->toRoute('publisher/zone',array("param1" => $DomainID));
    }
    
    /**
     * Reject an Ad space.
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function rejectAction()
    {
        $this->initialize();
        $DomainID = intval($this->params()->fromRoute('param1', 0));
        $this->adApprovalToggle(2);
        return $this->redirect()->toRoute('publisher/zone',array("param1" => $DomainID));
        
    }
    
    /**
     * Ad Tag generation for zone.
     *
     * @return Ad Tag
     */
     public function generateTagAction()
     {
     	
        $this->initialize();
        $request = $this->getRequest();
        
        if ($request->isPost()):
        
          $PublisherAdZoneID = $this->getRequest()->getPost('ad_id');
          $PublisherWebsiteID = intval($this->params()->fromRoute('param1', 0));
          
          $PublisherAdZoneFactory = \_factory\PublisherAdZone::get_instance();
          $PublisherWebsiteFactory = \_factory\PublisherWebsite::get_instance();
          
          $params = array();
		  $params["PublisherAdZoneID"] = $PublisherAdZoneID;
          $AdObject = $PublisherAdZoneFactory->get_row_object($params);
          
          $params = array();
		  $params["PublisherWebsiteID"] = $PublisherWebsiteID;
          $PublishObject = $PublisherWebsiteFactory->get_row_object($params);
          
          $width = 0;
          $height = 0;
          $domain = $PublishObject->WebDomain;
          if($AdObject->AdTemplateID != NULL && $AdObject->AdTemplateID != 0):
          
          	$AdTemplatesFactory = \_factory\AdTemplates::get_instance();
	        $params = array();
	        $params['AdTemplateID'] = $AdObject->AdTemplateID;
	        $AdTemplatesObject = $AdTemplatesFactory->get_row_object($params);
          	$height = $AdTemplatesObject->Height;
          	$width = $AdTemplatesObject->Width;
          
          else:
          	
          	$height = $AdObject->Height;
            $width = $AdObject->Width;
          
          endif;  
          
          $delivery_adtag = $this->config_handle['delivery']['adtag'];
          
          $cache_buster = time();
          	
          $effective_tag = "<script type='text/javascript' src='" . $delivery_adtag . "?pzoneid=" . $PublisherAdZoneID . "&height=" . $height . "&width=" . $width . "&tld=" . $domain . "&cb=" . $cache_buster . "'></script>";
          
          $data = array(
	        'result' => true,
	        'data' => array('tag' => htmlentities($effective_tag))
   		 );
          
          return $this->getResponse()->setContent(json_encode($data));

        endif;
     }
         
     private function getPublisherAdZoneTypeOptions() {
     
     	$PublisherAdZoneTypeFactory = \_factory\PublisherAdZoneType::get_instance();
     	$params = array();
     	$PublisherAdZoneTypeList = $PublisherAdZoneTypeFactory->get($params);
     	     	
     	$publisheradzonetype_options = array();
     
     	foreach ($PublisherAdZoneTypeList as $PublisherAdZoneType):
     
     		$publisheradzonetype_options[$PublisherAdZoneType->PublisherAdZoneTypeID] = $PublisherAdZoneType->Description;
     
     	endforeach;
     
     	return $publisheradzonetype_options;
     }
     
     /**
      *
      * @return JSON encoded data for AJAX call
      */
     public function editlinkedbannerAction() {
     
     	$this->initialize();
     	     	
     	$id 		= intval($this->params()->fromRoute('id', 0));
     	$height 	= $this->getRequest()->getQuery('height');
     	$width 		= $this->getRequest()->getQuery('width');
     
     	if ($height == null || $width == null):
     		die("Invalid Request");
     	endif;

     	// verify
     	$linked_ad_banners = array();
     
     	$PublisherAdZoneFactory = \_factory\PublisherAdZone::get_instance();
     	
     	if ($id):
     	
	     	$params = array("PublisherAdZoneID" => $id);
	     	$PublisherAdZone = $PublisherAdZoneFactory->get_row_object($params);
	     	
	     	if ($PublisherAdZone == null || $PublisherAdZone->AdOwnerID != $this->PublisherInfoID):
	     		$error_message = "An invalid Ad Zone ID was provided.";
		     	$data = array(
		     			'success' => false,
		     			'error'	=> $error_message,
		     			'linked_ad_zones' => "",
		     			'complete_zone_list' => array()
		     	);
		     	return $this->getResponse()->setContent(json_encode($data));
	     	endif;
	     	
	     	$LinkedBannerToAdZoneFactory = \_factory\LinkedBannerToAdZone::get_instance();
	     	$params = array();
	     	$params["PublisherAdZoneID"] = $id;
	     	$linked_ad_banners = $LinkedBannerToAdZoneFactory->get($params);
	     	
		endif;

     	$params = array();
     	$params["Height"] 	= $height;
     	$params["Width"] 	= $width;
     	$params["Active"] 	= 1;
     	$params["UserID"] 	= $this->EffectiveID;
     
     	$AdCampaignBannerFactory = \_factory\AdCampaignBanner::get_instance();
     	$AdCampaignBannerList = $AdCampaignBannerFactory->get($params);
     	if ($AdCampaignBannerList === null):
     		$AdCampaignBannerList = array();
     	endif;
     
     	$complete_banner_list = array();
     
     	foreach ($AdCampaignBannerList as $AdCampaignBanner):
     
	     	$complete_banner_list[] = array(
	     			"banner_id"	=>	$AdCampaignBanner->AdCampaignBannerID,
	     			"ad_name"	=>	$AdCampaignBanner->Name
	     	);
	     
     	endforeach;
     
     	$linked_banner_list = array();
     
     	foreach ($linked_ad_banners as $linked_ad_banner):
     
     		$linked_banner_list[] = $linked_ad_banner->AdCampaignBannerID;
     		
     	endforeach;
     
     	$data = array(
     			'success' => count($AdCampaignBannerList) > 0,
     			'linked_ad_banners' => implode(',', $linked_banner_list),
     			'complete_banner_list' => $complete_banner_list
     	);
     
     	return $this->getResponse()->setContent(json_encode($data));
     
     }
     
}