<?php
/**
 * CDNPAL NGINAD Project
 *
 * @link http://www.nginad.com
 * @copyright Copyright (c) 2013-2015 CDNPAL Ltd. All Rights Reserved
 * @license GPLv3
 */
namespace DashboardManager\Controller;

use DashboardManager\ParentControllers\DemandAbstractActionController;
use Zend\View\Model\ViewModel;
//use Zend\Session\Container; // We need this when using sessions (No longer used?)
use transformation;
use Zend\Mail\Message;
use Zend\Mime;

/**
 * @author Christopher Gu
 * This is the Demand Manager Controller class that controls the management
 * of demand functions.
 */
class DemandController extends DemandAbstractActionController {

    /**
     * Will Show the dashboard index page.
     * (non-PHPdoc)
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
	public function indexAction() {

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		$user_markup_rate = $this->config_handle['system']['default_demand_markup_rate'];
		$campaign_markup_rate_list = array();

		if ($this->is_super_admin):

		    // admin is logged in as a user, get the markup if any for that user
		    if ($this->ImpersonateID != 0 && !empty($this->DemandCustomerInfoID)):
		    
		    	$user_markup = \util\Markup::getMarkupForUser($this->auth->getEffectiveIdentityID(), $this->config_handle, false);
		    	if ($user_markup != null):
		   			$user_markup_rate = $user_markup->MarkupRate;
				endif;
		    endif;

		endif;

	    $InsertionOrderFactory = \_factory\InsertionOrder::get_instance();
	    $params = array();
	    $params["Active"] = 1;
	    // admin should see campaigns requiring approval and the user they belong to ONLY
	    $params["UserID"] = $this->auth->getEffectiveUserID();

	    $_ad_campaign_list = $InsertionOrderFactory->get($params);
	    $InsertionOrderPreviewFactory = \_factory\InsertionOrderPreview::get_instance();

	    $ad_campaign_list = array();

	    // admin should see campaigns requiring approval and the user they belong to ONLY
	    foreach ($_ad_campaign_list as $ad_campaign):
		  	$is_preview = \transformation\TransformPreview::doesPreviewInsertionOrderExistForInsertionOrder($ad_campaign->InsertionOrderID, $this->auth);
		 	if ($is_preview != true):
		    	$ad_campaign_list[] = $ad_campaign;
		 		$ad_campaign_markup = \util\Markup::getMarkupForInsertionOrder($ad_campaign->InsertionOrderID, $this->config_handle, false);

		 		if ($ad_campaign_markup != null):
		 			$campaign_markup_rate_list[$ad_campaign->InsertionOrderID] = $ad_campaign_markup->MarkupRate * 100;
		 		else:
		 			$campaign_markup_rate_list[$ad_campaign->InsertionOrderID] = $user_markup_rate * 100;
		 		endif;

		 	endif;
		endforeach;

	    // get previews
	    $params = array();
	    $params["Active"] = 1;
	    // $params["Deleted"] = 0;
	    if ($this->is_super_admin == true && $this->auth->getEffectiveIdentityID() != 0):
	   		$params["UserID"] = $this->auth->getEffectiveUserID();
	    elseif ($this->is_super_admin == false):
	    	$params["UserID"] = $this->auth->getUserID();
	    endif;
	    
	    $_ad_campaign_preview_list = $InsertionOrderPreviewFactory->get($params);

	    foreach ($_ad_campaign_preview_list as $ad_campaign_preview):
		    if ($ad_campaign_preview != null):
		    	$ad_campaign_list[] = $ad_campaign_preview;
		    	if ($ad_campaign_preview->InsertionOrderID != null):

				    $ad_campaign_markup = \util\Markup::getMarkupForInsertionOrder($ad_campaign_preview->InsertionOrderID, $this->config_handle, false);

				    if ($ad_campaign_markup != null):
				    	$campaign_markup_rate_list[$ad_campaign_preview->InsertionOrderID] = $ad_campaign_markup->MarkupRate * 100;
				    else:
				    	$campaign_markup_rate_list[$ad_campaign_preview->InsertionOrderID] = $user_markup_rate * 100;
				    endif;

			    endif;
		    endif;
	    endforeach;

	    $user_markup_rate *= 100;

	    $view = new ViewModel(array(
	    		'ad_campaigns' => $ad_campaign_list,
	    		'is_super_admin' => $this->auth->isSuperAdmin($this->config_handle),
	    		'user_id_list' => $this->user_id_list_demand_customer,
	    		'effective_id' => $this->auth->getEffectiveIdentityID(),
	    		'campaign_markup_rate_list'=>$campaign_markup_rate_list,
	    		'user_markup_rate' => $user_markup_rate,
	    		'dashboard_view' => 'demand',
	    		'user_identity' => $this->identity(),
	    		'true_user_name' => $this->auth->getUserName(),
				'is_super_admin' => $this->is_super_admin,
				'effective_id' => $this->auth->getEffectiveIdentityID(),
				'impersonate_id' => $this->ImpersonateID

	    ));
	    
	    if ($this->is_super_admin == false 
	    	|| ($this->is_super_admin == true && $this->DemandCustomerInfoID != null && $this->auth->getEffectiveIdentityID() != 0)):
	    	
	    	$view->header_title = '<a href="/demand/createinsertionorder/">Create Insertion Order</a>';
	    else:
	   		$view->header_title = '&nbsp;';
	    endif;

	    return $view;
	}

	/**
	 * Allows an administrator to "login as another user", to impersonate a lower user to manage another user's objects.
	 * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
	 */
	public function loginasAction()
	{
	    $this->ImpersonateUser();
		return $this->redirect()->toRoute('demand');
	}

	/**
	 * 
	 * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
	 */
	public function changeusermarkupAction() {
		
		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		if ($this->auth->isSuperAdmin($this->config_handle)):
			die("You do not have permission to access this page");
		endif;

		$user_id 		= $this->getRequest()->getQuery('markupuserid');
		$user_markup 	= $this->getRequest()->getQuery('user-markup');

		$UserMarkupDemandFactory = \_factory\UserMarkupDemand::get_instance();
		$params = array();
		$params["UserID"] = $user_id;
		$UserMarkupDemand = $UserMarkupDemandFactory->get_row($params);

		$user_markup = floatval($user_markup) / 100;

			if ($user_markup <= 0):
				die("User Markup can not be less than or equal to zero percent");
			endif;

			if ($user_markup >= 1):
				die("User Markup can not be greater than or equal to one hundred percent");
			endif;

		$user_markup = sprintf("%1.2f", $user_markup);

		$_UserMarkupDemand = new \model\UserMarkupDemand();
		$_UserMarkupDemand->UserID 	= $user_id;
		$_UserMarkupDemand->MarkupRate = $user_markup;

			if ($UserMarkupDemand != null):
	
				$UserMarkupDemandFactory->updateUserMarkupDemand($_UserMarkupDemand);
	
			else:
	
				$UserMarkupDemandFactory->insertUserMarkupDemand($_UserMarkupDemand);
	
			endif;

		return $this->redirect()->toRoute('demand');

	}

	/**
	 * 
	 * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
	 */
	public function changecampaignmarkupAction() {
		
		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		if ($this->auth->isSuperAdmin($this->config_handle)):
			die("You do not have permission to access this page");
		endif;

		$campaign_id 		= $this->getRequest()->getQuery('markupcampaignid');
		$campaign_markup 	= $this->getRequest()->getQuery('campaign-markup');

		$InsertionOrderMarkupFactory = \_factory\InsertionOrderMarkup::get_instance();
		$params = array();
		$params["InsertionOrderID"] = $campaign_id;
		$InsertionOrderMarkup = $InsertionOrderMarkupFactory->get_row($params);

		$campaign_markup = floatval($campaign_markup) / 100;

			if ($campaign_markup <= 0):
				die("Campaign Markup can not be less than or equal to zero percent");
			endif;

			if ($campaign_markup >= 1):
				die("Campaign Markup can not be greater than or equal to one hundred percent");
			endif;

		$campaign_markup = sprintf("%1.2f", $campaign_markup);

		$_InsertionOrderMarkup = new \model\InsertionOrderMarkup();
		$_InsertionOrderMarkup->InsertionOrderID 	= $campaign_id;
		$_InsertionOrderMarkup->MarkupRate 		= $campaign_markup;

			if ($InsertionOrderMarkup != null):
	
				$InsertionOrderMarkupFactory->updateInsertionOrderMarkup($_InsertionOrderMarkup);
	
			else:
	
				$InsertionOrderMarkupFactory->insertInsertionOrderMarkup($_InsertionOrderMarkup);
	
			endif;

		return $this->redirect()->toRoute('demand');

	}

	/**
	 * 
	 * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
	 */
	public function approvecampaignAction() {
		
		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		if ($this->auth->isSuperAdmin($this->config_handle)):
			die("You do not have permission to access this page");
		endif;

		$id = $this->getEvent()->getRouteMatch()->getParam('param1');
		if ($id == null):
			die("Invalid Campaign Preview ID");
		endif;

		// copy the preview campaign and its elements into the production campaign
		$ad_campaign_id = \transformation\TransformPreview::cloneInsertionOrderPreviewIntoInsertionOrder($id, $this->auth, $this->config_handle);
		// set the preview campaigns and its elements to inactive and mark the date and time they went live
		\transformation\TransformPreview::deletePreviewModeCampaign($id, $this->auth, true);

		$InsertionOrderFactory = \_factory\InsertionOrder::get_instance();
		$params = array();
		$params["InsertionOrderID"] = $ad_campaign_id;
		$InsertionOrder = $InsertionOrderFactory->get_row($params);
		
		if ($InsertionOrder == null):
			return $this->redirect()->toRoute('demand');
		endif;
		
        $authUsersFactory = \_factory\authUsers::get_instance();
        $params = array();
        $params["user_id"] = $InsertionOrder->UserID; 
        $auth_User = $authUsersFactory->get_row($params);
		
        if ($auth_User !== null && $this->config_handle['mail']['subscribe']['user_ad_campaigns']):
			// approval, send out email
			$message = 'Your NginAd Exchange Demand Ad Campaign : ' . $InsertionOrder->Name . ' was approved.<br /><br />Please login <a href="http://server.nginad.com/auth/login">here</a> with your email and password';
			
			$subject = "Your NginAd Exchange Demand Ad Campaign : " . $InsertionOrder->Name . " was approved";
			 
			$transport = $this->getServiceLocator()->get('mail.transport');
			 
			$text = new Mime\Part($message);
			$text->type = Mime\Mime::TYPE_HTML;
			$text->charset = 'utf-8';
			 
			$mimeMessage = new Mime\Message();
			$mimeMessage->setParts(array($text));
			$zf_message = new Message();
			$zf_message->addTo($auth_User->user_email)
			->addFrom($this->config_handle['mail']['reply-to']['email'], $this->config_handle['mail']['reply-to']['name'])
			->setSubject($subject)
			->setBody($mimeMessage);
			$transport->send($zf_message);
		endif;
		
		return $this->redirect()->toRoute('demand');

	}

	/**
	 * 
	 * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
	 */
	public function cancelcampaignAction() {
		$id = $this->getEvent()->getRouteMatch()->getParam('param1');
		if ($id == null):
			die("Invalid Campaign Preview ID");
		endif;

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		// ACL PREVIEW PERMISSIONS CHECK
		transformation\CheckPermissions::checkEditPermissionInsertionOrderPreview($id, $this->auth, $this->config_handle);

		// set the preview campaigns and its elements to inactive and mark the date and time they went live
		\transformation\TransformPreview::deletePreviewModeCampaign($id, $this->auth, false);

		return $this->redirect()->toRoute('demand');

	}

	/**
	 * 
	 * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
	 */
	public function rejectcampaignAction() {
		
		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		if ($this->auth->isSuperAdmin($this->config_handle)):
			die("You do not have permission to access this page");
		endif;

		$id = $this->getEvent()->getRouteMatch()->getParam('param1');
		if ($id == null):
			die("Invalid Campaign Preview ID");
		endif;

		$InsertionOrderPreviewFactory = \_factory\InsertionOrderPreview::get_instance();
		$params = array();
		$params["InsertionOrderPreviewID"] = $id;
		$InsertionOrderPreview = $InsertionOrderPreviewFactory->get_row($params);
		
		if ($InsertionOrderPreview == null):
			die("InsertionOrderPreviewID not found");
		endif;	
		
		$ad_campaign_preview_name = $InsertionOrderPreview->Name;
		$user_id = $InsertionOrderPreview->UserID;
		
		// set the preview campaigns and its elements to inactive and mark the date and time they went live
		\transformation\TransformPreview::deletePreviewModeCampaign($id, $this->auth, false);
		
		$authUsersFactory = \_factory\authUsers::get_instance();
		$params = array();
		$params["user_id"] = $user_id; 
		$auth_User = $authUsersFactory->get_row($params);
		
		if ($auth_User !== null && $ad_campaign_preview_name && $this->config_handle['mail']['subscribe']['user_ad_campaigns']):
			// approval, send out email
			$message = 'Your NginAd Exchange Demand Ad Campaign : ' . $ad_campaign_preview_name . ' was rejected.<br /><br />Please login <a href="http://server.nginad.com/auth/login">here</a> with your email and password';
				
			$subject = "Your NginAd Exchange Demand Ad Campaign : " . $ad_campaign_preview_name . " was rejected";
			
			$transport = $this->getServiceLocator()->get('mail.transport');
			
			$text = new Mime\Part($message);
			$text->type = Mime\Mime::TYPE_HTML;
			$text->charset = 'utf-8';
			
			$mimeMessage = new Mime\Message();
			$mimeMessage->setParts(array($text));
			$zf_message = new Message();
			$zf_message->addTo($auth_User->user_email)
			->addFrom($this->config_handle['mail']['reply-to']['email'], $this->config_handle['mail']['reply-to']['name'])
			->setSubject($subject)
			->setBody($mimeMessage);
			$transport->send($zf_message);
		endif;
		
		return $this->redirect()->toRoute('demand');

	}

	/*
	 * BEGIN NGINAD InsertionOrderLineItemRestrictions Actions
	 */

	/**
	 * 
	 * @return Ambigous <\Zend\View\Model\ViewModel, \Zend\View\Model\ViewModel>
	 */
	public function deletedeliveryfilterAction() {
		
		$error_msg = null;
		$success = true;
		$id = $this->getEvent()->getRouteMatch()->getParam('param1');
		if ($id == null):
			//die("Invalid Banner ID");
			$error_msg = "Invalid Banner ID";
			$success = false;
			$data = array(
					'success' => $success,
					'data' => array('error_msg' => $error_msg)
			);
			
			return $this->getResponse()->setContent(json_encode($data));
		endif;

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		// ACL PERMISSIONS CHECK
		//transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItem($id, $auth, $config);
		$ispreview 				= $this->getRequest()->getQuery('ispreview');
		
		if ($ispreview != "true"):
			/*
			 * THIS METHOD CHECKS IF THERE IS AN EXISTING PREVIEW MODE CAMPAIGN
			* IF NOT, IT CHECKS THE ACL PERMISSIONS ON THE PRODUCTION BANNER/CAMPAIGN REFERENCED
			* THEN IT CREATES A PREVIEW VERSION OF THE AD CAMPAIGN
			*/
			$update_data = array('type'=>'InsertionOrderLineItemID', 'id'=>$id);
			$return_val = \transformation\TransformPreview::previewCheckBannerID($id, $this->auth, $this->config_handle, $this->getServiceLocator()->get('mail.transport'), $update_data);
			
			if ($return_val !== null):
				$id = $return_val["InsertionOrderLineItemPreviewID"];
			else:
				$success = false;
				$data = array(
						'success' => $success,
						'data' => array('error_msg' => 'id not found')
				);
				
				return $this->getResponse()->setContent(json_encode($data));
			endif;
		
		endif;
		
		$response = transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($id, $this->auth, $this->config_handle);
		
		if(array_key_exists("error", $response) > 0):
			$success = false;
			$data = array(
		       'success' => $success,
		       'data' => array('error_msg' => $response['error'])
	   		);
	   		
	   	   return $this->getResponse()->setContent(json_encode($data));
		endif;
		
		$InsertionOrderLineItemPreviewFactory = \_factory\InsertionOrderLineItemPreview::get_instance();
		$InsertionOrderLineItemVideoRestrictionsPreviewFactory = \_factory\InsertionOrderLineItemVideoRestrictionsPreview::get_instance();
		$InsertionOrderLineItemRestrictionsPreviewFactory = \_factory\InsertionOrderLineItemRestrictionsPreview::get_instance();
		
		$InsertionOrderLineItemRestrictionsPreviewFactory->deleteInsertionOrderLineItemRestrictionsPreview($id);
		$InsertionOrderLineItemVideoRestrictionsPreviewFactory->deleteInsertionOrderLineItemVideoRestrictionsPreview($id);
		
		$params = array();
		$params["InsertionOrderLineItemPreviewID"] = $id;
		$InsertionOrderLineItemPreview = $InsertionOrderLineItemPreviewFactory->get_row($params);
		
		$success = true;
		$data = array(
		     'success' => $success,
			 'location' => '/demand/viewlineitem/',
			 'previewid' => $InsertionOrderLineItemPreview->InsertionOrderPreviewID,
		     'data' => array('error_msg' => $error_msg)
	   	);
   		 
        return $this->getResponse()->setContent(json_encode($data));

	}

	public function editdeliveryfiltervideoAction() {
	
		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;
	
		$needed_input = array(
				'ispreview'
		);
	
		$this->validateInput($needed_input);
	
		$bannerid 				= $this->getRequest()->getPost('bannerid');
		$banner_preview_id 		= $this->getRequest()->getPost('bannerpreviewid');
		$ispreview 				= $this->getRequest()->getPost('ispreview');
	
		if ($ispreview != true):
			/*
			 * THIS METHOD CHECKS IF THERE IS AN EXISTING PREVIEW MODE CAMPAIGN
			* IF NOT, IT CHECKS THE ACL PERMISSIONS ON THE PRODUCTION BANNER/CAMPAIGN REFERENCED
			* THEN IT CREATES A PREVIEW VERSION OF THE AD CAMPAIGN
			*/
			$update_data = array('type'=>'InsertionOrderLineItemID', 'id'=>$bannerid);
			$return_val = \transformation\TransformPreview::previewCheckBannerID($bannerid, $this->auth, $this->config_handle, $this->getServiceLocator()->get('mail.transport'), $update_data);
		
			if ($return_val !== null):
				$banner_preview_id = $return_val["InsertionOrderLineItemPreviewID"];
			endif;
		
		endif;
	
		// ACL PREVIEW PERMISSIONS CHECK
		transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($banner_preview_id, $this->auth, $this->config_handle);

		$start_delay 				= $this->getRequest()->getPost("StartDelay");
			
		$fold_pos 					= $this->getRequest()->getPost("FoldPos");

		$vertical 					= $this->getRequest()->getPost("vertical");
		
		$geocountry 				= $this->getRequest()->getPost("geocountry");
		
		$geostate 					= $this->getRequest()->getPost("geostate");
		
		$geocity 					= $this->getRequest()->getPost("geocity");
		
		$pmpenable 					= $this->getRequest()->getPost("pmpenable");
		
		$secure 					= $this->getRequest()->getPost("secure");
			
		$optout 					= $this->getRequest()->getPost("optout");
		
		$min_duration 				= $this->getRequest()->getPost("MinDuration");
		
		$max_duration 				= $this->getRequest()->getPost("MaxDuration");
			
		$min_height 				= $this->getRequest()->getPost("MinHeight");
		
		$min_width 					= $this->getRequest()->getPost("MinWidth");

		$linearity 					= $this->getRequest()->getPost("Linearity");
		
		
		$mimes 						= $this->getRequest()->getPost("Mimes");
		if ($mimes && is_array($mimes) && count($mimes) > 0):
			$mimes = join(',', $mimes);
		endif;
			
		$protocols 					= $this->getRequest()->getPost("Protocols");
		if ($protocols && is_array($protocols) && count($protocols) > 0):
			$protocols = join(',', $protocols);
		endif;

		$apis_supported 			= $this->getRequest()->getPost("ApisSupported");
		if ($apis_supported && is_array($apis_supported) && count($apis_supported) > 0):
			$apis_supported = join(',', $apis_supported);
		endif;
			
		$delivery 					= $this->getRequest()->getPost("Delivery");
		if ($delivery && is_array($delivery) && count($delivery) > 0):
			$delivery = join(',', $delivery);
		endif;
			
		$playback 					= $this->getRequest()->getPost("Playback");
		if ($playback && is_array($playback) && count($playback) > 0):
			$playback = join(',', $playback);
		endif;
			
		if ($vertical && is_array($vertical) && count($vertical) > 0):
	
			$vertical = join(',', $vertical);
	
		endif;
	
		if ($geocountry && is_array($geocountry) && count($geocountry) > 0):
	
			$geocountry = join(',', $geocountry);
	
		endif;
	
		if ($geostate && is_array($geostate) && count($geostate) > 0):
	
			$geostate = join(',', $geostate);
	
		endif;
	
		if (strpos($geocity, ",") !== false):
		
			$geocities = explode(",", $geocity);
		
			$geocity_list_trimmed = array();
		
			foreach ($geocities as $geocityitem):
		
				$geocity_list_trimmed[] = trim($geocityitem);
		
			endforeach;
		
			$geocity = join(',', $geocity_list_trimmed);
		
		endif;
	
		$InsertionOrderLineItemVideoRestrictionsPreviewFactory = \_factory\InsertionOrderLineItemVideoRestrictionsPreview::get_instance();
		$params = array();
		$params["InsertionOrderLineItemPreviewID"] = $banner_preview_id;
	
		$InsertionOrderLineItemVideoRestrictionsPreview = $InsertionOrderLineItemVideoRestrictionsPreviewFactory->get_row($params);
	
		$VideoRestrictionsPreview = new \model\InsertionOrderLineItemVideoRestrictionsPreview();
	
		if ($InsertionOrderLineItemVideoRestrictionsPreview != null):
	
			$VideoRestrictionsPreview->InsertionOrderLineItemVideoRestrictionsPreviewID            = $InsertionOrderLineItemVideoRestrictionsPreview->InsertionOrderLineItemVideoRestrictionsPreviewID;
	
		endif;
	
		$VideoRestrictionsPreview->InsertionOrderLineItemPreviewID                = $banner_preview_id;
		$VideoRestrictionsPreview->Vertical                                 = trim($vertical);
		$VideoRestrictionsPreview->GeoCountry                               = trim($geocountry);
		$VideoRestrictionsPreview->GeoState                                 = trim($geostate);
		$VideoRestrictionsPreview->GeoCity                                  = trim($geocity);
		$VideoRestrictionsPreview->PmpEnable                                = trim($pmpenable);
		$VideoRestrictionsPreview->Secure                                   = trim($secure);
		$VideoRestrictionsPreview->Optout                                   = trim($optout);
		$VideoRestrictionsPreview->MinDuration                              = trim($min_duration);
		$VideoRestrictionsPreview->MaxDuration                              = trim($max_duration);
		$VideoRestrictionsPreview->MinHeight                              	= trim($min_height);
		$VideoRestrictionsPreview->MinWidth                              	= trim($min_width);
		$VideoRestrictionsPreview->MimesCommaSeparated                      = trim($mimes);
		$VideoRestrictionsPreview->ProtocolsCommaSeparated                 	= trim($protocols);
		$VideoRestrictionsPreview->ApisSupportedCommaSeparated            	= trim($apis_supported);
		$VideoRestrictionsPreview->DeliveryCommaSeparated                 	= trim($delivery);
		$VideoRestrictionsPreview->PlaybackCommaSeparated              		= trim($playback);
		$VideoRestrictionsPreview->StartDelay                              	= trim($start_delay);
		$VideoRestrictionsPreview->Linearity                              	= trim($linearity);
		$VideoRestrictionsPreview->FoldPos                              	= trim($fold_pos);
		$VideoRestrictionsPreview->DateCreated                              = date("Y-m-d H:i:s");
		$VideoRestrictionsPreview->DateUpdated                              = date("Y-m-d H:i:s");

		$InsertionOrderLineItemVideoRestrictionsPreviewFactory = \_factory\InsertionOrderLineItemVideoRestrictionsPreview::get_instance();
		$InsertionOrderLineItemVideoRestrictionsPreviewFactory->saveInsertionOrderLineItemVideoRestrictionsPreview($VideoRestrictionsPreview);
	
		$InsertionOrderLineItemPreviewFactory = \_factory\InsertionOrderLineItemPreview::get_instance();
		$params = array();
		$params["InsertionOrderLineItemPreviewID"] = $banner_preview_id;
	
		$InsertionOrderLineItemPreview = $InsertionOrderLineItemPreviewFactory->get_row($params);
	
		$refresh_url = "/demand/viewlineitem/" . $InsertionOrderLineItemPreview->InsertionOrderPreviewID . "?ispreview=true";
		$viewModel = new ViewModel(array('refresh_url' => $refresh_url));
	
		return $viewModel->setTemplate('dashboard-manager/demand/interstitial.phtml');
	}
	
	/**
	 * 
	 * @return Ambigous <\Zend\View\Model\ViewModel, \Zend\View\Model\ViewModel>
	 */
	public function editdeliveryfilterAction() {

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		$needed_input = array(
				'ispreview'
		);

		$this->validateInput($needed_input);

		$bannerid 				= $this->getRequest()->getPost('bannerid');
		$banner_preview_id 		= $this->getRequest()->getPost('bannerpreviewid');
		$ispreview 				= $this->getRequest()->getPost('ispreview');

			if ($ispreview != true):
				/*
				 * THIS METHOD CHECKS IF THERE IS AN EXISTING PREVIEW MODE CAMPAIGN
				* IF NOT, IT CHECKS THE ACL PERMISSIONS ON THE PRODUCTION BANNER/CAMPAIGN REFERENCED
				* THEN IT CREATES A PREVIEW VERSION OF THE AD CAMPAIGN
				*/
				$update_data = array('type'=>'InsertionOrderLineItemID', 'id'=>$bannerid);
				$return_val = \transformation\TransformPreview::previewCheckBannerID($bannerid, $this->auth, $this->config_handle, $this->getServiceLocator()->get('mail.transport'), $update_data);
	
				if ($return_val !== null):
					$banner_preview_id = $return_val["InsertionOrderLineItemPreviewID"];
				endif;
	
			endif;

		// ACL PREVIEW PERMISSIONS CHECK
		transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($banner_preview_id, $this->auth, $this->config_handle);

		$vertical = $this->getRequest()->getPost('vertical');
		$geocountry = $this->getRequest()->getPost('geocountry');
		$geostate = $this->getRequest()->getPost('geostate');
		$geocity = $this->getRequest()->getPost('geocity');
		$adtagtype = $this->getRequest()->getPost('adtagtype');
		$adpositionminleft = $this->getRequest()->getPost('adpositionminleft');
		$adpositionmaxleft = $this->getRequest()->getPost('adpositionmaxleft');
		$adpositionmintop = $this->getRequest()->getPost('adpositionmintop');
		$adpositionmaxtop = $this->getRequest()->getPost('adpositionmaxtop');
		$foldpos = $this->getRequest()->getPost('foldpos');
		$frequency = $this->getRequest()->getPost('frequency');
		$timezone = $this->getRequest()->getPost('timezone');
		$iniframe = $this->getRequest()->getPost('iniframe');
		$inmultiplenestediframes = $this->getRequest()->getPost('inmultiplenestediframes');
		$minscreenresolutionwidth = $this->getRequest()->getPost('minscreenresolutionwidth');
		$maxscreenresolutionwidth = $this->getRequest()->getPost('maxscreenresolutionwidth');
		$minscreenresolutionheight = $this->getRequest()->getPost('minscreenresolutionheight');
		$maxscreenresolutionheight = $this->getRequest()->getPost('maxscreenresolutionheight');
		$httplanguage = $this->getRequest()->getPost('httplanguage');
		$browseruseragentgrep = $this->getRequest()->getPost('browseruseragentgrep');
		$cookiegrep = $this->getRequest()->getPost('cookiegrep');
		$pmpenable = $this->getRequest()->getPost('pmpenable');
		$secure = $this->getRequest()->getPost('secure');
		$optout = $this->getRequest()->getPost('optout');


			if ($vertical && is_array($vertical) && count($vertical) > 0):
	
	            $vertical = join(',', $vertical);
	
			endif;

			if ($geocountry && is_array($geocountry) && count($geocountry) > 0):
	
			  $geocountry = join(',', $geocountry);
	
			endif;

			if ($geostate && is_array($geostate) && count($geostate) > 0):
	
			  $geostate = join(',', $geostate);
	
			endif;

			if (strpos($geocity, ",") !== false):
	
			  $geocities = explode(",", $geocity);
	
			  $geocity_list_trimmed = array();
	
			  foreach ($geocities as $geocityitem):
	
			      $geocity_list_trimmed[] = trim($geocityitem);
	
			  endforeach;
	
			  $geocity = join(',', $geocity_list_trimmed);
	
			endif;

			if ($timezone && is_array($timezone) && count($timezone) > 0):
	
			  $timezone = join(',', $timezone);
	
			endif;

		$InsertionOrderLineItemRestrictionsPreviewFactory = \_factory\InsertionOrderLineItemRestrictionsPreview::get_instance();
		$params = array();
		$params["InsertionOrderLineItemPreviewID"] = $banner_preview_id;

		$InsertionOrderLineItemRestrictionsPreview = $InsertionOrderLineItemRestrictionsPreviewFactory->get_row($params);

		$BannerRestrictionsPreview = new \model\InsertionOrderLineItemRestrictionsPreview();

			if ($InsertionOrderLineItemRestrictionsPreview != null):
	
			      $BannerRestrictionsPreview->InsertionOrderLineItemRestrictionsPreviewID            = $InsertionOrderLineItemRestrictionsPreview->InsertionOrderLineItemRestrictionsPreviewID;
	
			endif;

		$BannerRestrictionsPreview->InsertionOrderLineItemPreviewID                       = $banner_preview_id;
		$BannerRestrictionsPreview->GeoCountry                               = trim($geocountry);
		$BannerRestrictionsPreview->GeoState                                 = trim($geostate);
		$BannerRestrictionsPreview->GeoCity                                  = trim($geocity);
		$BannerRestrictionsPreview->AdTagType                                = trim($adtagtype);
		$BannerRestrictionsPreview->AdPositionMinLeft                        = trim($adpositionminleft);
		$BannerRestrictionsPreview->AdPositionMaxLeft                        = trim($adpositionmaxleft);
		$BannerRestrictionsPreview->AdPositionMinTop                         = trim($adpositionmintop);
		$BannerRestrictionsPreview->AdPositionMaxTop                         = trim($adpositionmaxtop);
		$BannerRestrictionsPreview->FoldPos                                  = trim($foldpos);
		$BannerRestrictionsPreview->Freq                                     = trim($frequency);
		$BannerRestrictionsPreview->Timezone                                 = trim($timezone);
		$BannerRestrictionsPreview->InIframe                                 = trim($iniframe);
		$BannerRestrictionsPreview->InMultipleNestedIframes                  = trim($inmultiplenestediframes);
		$BannerRestrictionsPreview->MinScreenResolutionWidth                 = trim($minscreenresolutionwidth);
		$BannerRestrictionsPreview->MaxScreenResolutionWidth                 = trim($maxscreenresolutionwidth);
		$BannerRestrictionsPreview->MinScreenResolutionHeight                = trim($minscreenresolutionheight);
		$BannerRestrictionsPreview->MaxScreenResolutionHeight                = trim($maxscreenresolutionheight);
		$BannerRestrictionsPreview->HttpLanguage                             = trim($httplanguage);
		$BannerRestrictionsPreview->BrowserUserAgentGrep                     = trim($browseruseragentgrep);
		$BannerRestrictionsPreview->CookieGrep                               = trim($cookiegrep);
		$BannerRestrictionsPreview->PmpEnable                                = trim($pmpenable);
		$BannerRestrictionsPreview->Secure                                   = trim($secure);
		$BannerRestrictionsPreview->Optout                                   = trim($optout);
		$BannerRestrictionsPreview->Vertical                                 = trim($vertical);
		$BannerRestrictionsPreview->DateCreated                              = date("Y-m-d H:i:s");
		$BannerRestrictionsPreview->DateUpdated                              = date("Y-m-d H:i:s");

		$InsertionOrderLineItemRestrictionsPreviewFactory = \_factory\InsertionOrderLineItemRestrictionsPreview::get_instance();
		$InsertionOrderLineItemRestrictionsPreviewFactory->saveInsertionOrderLineItemRestrictionsPreview($BannerRestrictionsPreview);

		$InsertionOrderLineItemPreviewFactory = \_factory\InsertionOrderLineItemPreview::get_instance();
		$params = array();
		$params["InsertionOrderLineItemPreviewID"] = $banner_preview_id;

		$InsertionOrderLineItemPreview = $InsertionOrderLineItemPreviewFactory->get_row($params);

		$refresh_url = "/demand/viewlineitem/" . $InsertionOrderLineItemPreview->InsertionOrderPreviewID . "?ispreview=true";
		$viewModel = new ViewModel(array('refresh_url' => $refresh_url));

		return $viewModel->setTemplate('dashboard-manager/demand/interstitial.phtml');
	}

	public function deliveryfiltervideoAction() {
		$id = $this->getEvent()->getRouteMatch()->getParam('param1');
		if ($id == null):
			die("Invalid Banner ID");
		endif;
	
		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;
	
		$is_preview = $this->getRequest()->getQuery('ispreview');
	
		// verify
		if ($is_preview == "true"):
			$is_preview = \transformation\TransformPreview::doesPreviewBannerExist($id, $this->auth);
		endif;
	
		$banner_preview_id = "";
		$campaign_id = "";
		$campaign_preview_id = "";
	
		if ($is_preview == true):
			// ACL PREVIEW PERMISSIONS CHECK
			transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($id, $this->auth, $this->config_handle);
		
			$InsertionOrderLineItemVideoRestrictionsPreviewFactory = \_factory\InsertionOrderLineItemVideoRestrictionsPreview::get_instance();
			$params = array();
			$params["InsertionOrderLineItemPreviewID"] = $id;
			$banner_preview_id = $id;
			$id = "";
			$InsertionOrderLineItemVideoRestrictions = $InsertionOrderLineItemVideoRestrictionsPreviewFactory->get_row($params);
		
			$InsertionOrderLineItemPreviewFactory = \_factory\InsertionOrderLineItemPreview::get_instance();
			$params = array();
			$params["InsertionOrderLineItemPreviewID"] = $banner_preview_id;
			$InsertionOrderLineItemPreview = $InsertionOrderLineItemPreviewFactory->get_row($params);
			$campaign_preview_id = $InsertionOrderLineItemPreview->InsertionOrderPreviewID;
		
		else:
			// ACL PERMISSIONS CHECK
			transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItem($id, $this->auth, $this->config_handle);
		
			$InsertionOrderLineItemVideoRestrictionsFactory = \_factory\InsertionOrderLineItemVideoRestrictions::get_instance();
			$params = array();
			$params["InsertionOrderLineItemID"] = $id;
		
			$InsertionOrderLineItemVideoRestrictions = $InsertionOrderLineItemVideoRestrictionsFactory->get_row($params);
		
			$InsertionOrderLineItemFactory = \_factory\InsertionOrderLineItem::get_instance();
			$params = array();
			$params["InsertionOrderLineItemID"] = $id;
			$InsertionOrderLineItem = $InsertionOrderLineItemFactory->get_row($params);
			$campaign_id = $InsertionOrderLineItem->InsertionOrderID;
		endif;
	
		$current_states 				= "";
		$current_country 				= "";
		$geocity_option 				= "";

		$current_min_duration 			= "";
		$current_max_duration 			= "";
		
		$current_min_height 			= "";
		$current_min_width	 			= "";
		
		$current_mimes 					= array();
		$current_apis_supported 		= array();
		$current_protocols 				= array();
		$current_delivery_methods 		= array();
		$current_playback_methods 		= array();
		
		$current_start_delay 			= "";
		$current_linearity 				= array();
		$current_foldpos 				= "";
		
		$current_pmpenable 				= "";
		$current_secure 				= "";
		$current_optout 				= "";
		$current_vertical 				= array();
	
		$current_mimes_raw 				= "";
		$current_apis_supported_raw 	= "";
		$current_protocols_raw 			= "";
		$current_delivery_methods_raw 	= "";
		$current_playback_methods_raw 	= "";
		$current_vertical_raw 			= "";
		
		if ($InsertionOrderLineItemVideoRestrictions != null):
		
			$current_foldpos = $InsertionOrderLineItemVideoRestrictions->Vertical == null ? "" : $InsertionOrderLineItemVideoRestrictions->Vertical;
			$current_states = $InsertionOrderLineItemVideoRestrictions->GeoState == null ? "" : $InsertionOrderLineItemVideoRestrictions->GeoState;
			$current_country = $InsertionOrderLineItemVideoRestrictions->GeoCountry == null ? "" : $InsertionOrderLineItemVideoRestrictions->GeoCountry;
			$geocity_option = $InsertionOrderLineItemVideoRestrictions->GeoCity == null ? "" : $InsertionOrderLineItemVideoRestrictions->GeoCity;
			
			$current_mimes_raw = $InsertionOrderLineItemVideoRestrictions->MimesCommaSeparated == null ? "" : $InsertionOrderLineItemVideoRestrictions->MimesCommaSeparated;
			$current_apis_supported_raw = $InsertionOrderLineItemVideoRestrictions->ApisSupportedCommaSeparated == null ? "" : $InsertionOrderLineItemVideoRestrictions->ApisSupportedCommaSeparated;
			$current_protocols_raw = $InsertionOrderLineItemVideoRestrictions->ProtocolsCommaSeparated == null ? "" : $InsertionOrderLineItemVideoRestrictions->ProtocolsCommaSeparated;
			$current_delivery_methods_raw = $InsertionOrderLineItemVideoRestrictions->DeliveryCommaSeparated == null ? "" : $InsertionOrderLineItemVideoRestrictions->DeliveryCommaSeparated;
			$current_playback_methods_raw = $InsertionOrderLineItemVideoRestrictions->PlaybackCommaSeparated == null ? "" : $InsertionOrderLineItemVideoRestrictions->PlaybackCommaSeparated;
			
			$current_start_delay = $InsertionOrderLineItemVideoRestrictions->StartDelay == null ? "" : $InsertionOrderLineItemVideoRestrictions->StartDelay;
			$current_linearity = $InsertionOrderLineItemVideoRestrictions->Linearity == null ? "" : $InsertionOrderLineItemVideoRestrictions->Linearity;
			$current_fold_pos = $InsertionOrderLineItemVideoRestrictions->FoldPos == null ? "" : $InsertionOrderLineItemVideoRestrictions->FoldPos;

			$current_min_duration = $InsertionOrderLineItemVideoRestrictions->MinDuration == null ? "" : $InsertionOrderLineItemVideoRestrictions->MinDuration;
			$current_max_duration = $InsertionOrderLineItemVideoRestrictions->MaxDuration == null ? "" : $InsertionOrderLineItemVideoRestrictions->MaxDuration;
			
			$current_min_height = $InsertionOrderLineItemVideoRestrictions->MinHeight == null ? "" : $InsertionOrderLineItemVideoRestrictions->MinHeight;
			$current_min_width = $InsertionOrderLineItemVideoRestrictions->MinWidth == null ? "" : $InsertionOrderLineItemVideoRestrictions->MinWidth;

			$current_pmpenable = $InsertionOrderLineItemVideoRestrictions->PmpEnable == null ? "" : $InsertionOrderLineItemVideoRestrictions->PmpEnable;
			$current_secure = $InsertionOrderLineItemVideoRestrictions->Secure == null ? "" : $InsertionOrderLineItemVideoRestrictions->Secure;
			$current_optout = $InsertionOrderLineItemVideoRestrictions->Optout == null ? "" : $InsertionOrderLineItemVideoRestrictions->Optout;
			$current_vertical_raw = $InsertionOrderLineItemVideoRestrictions->Vertical == null ? "" : $InsertionOrderLineItemVideoRestrictions->Vertical;
			
		endif;
	
		$current_mimes = array();
		
		if ($current_mimes_raw):
		
			$current_mimes = explode(',', $current_mimes_raw);
		
		endif;
		
		$current_apis_supported = array();
		
		if ($current_apis_supported_raw):
		
			$current_apis_supported = explode(',', $current_apis_supported_raw);
		
		endif;
		
		$current_protocols = array();
		
		if ($current_protocols_raw):
		
			$current_protocols = explode(',', $current_protocols_raw);
		
		endif;
		
		$current_delivery_methods = array();
		
		if ($current_delivery_methods_raw):
		
			$current_delivery_methods = explode(',', $current_delivery_methods_raw);
		
		endif;
		
		$current_playback_methods = array();
		
		if ($current_playback_methods_raw):
		
			$current_playback_methods = explode(',', $current_playback_methods_raw);
		
		endif;
		
		$current_verticals = array();
	
		if ($current_vertical_raw):
	
			$current_verticals = explode(',', $current_vertical_raw);
	
		endif;
	
		$current_countries = array();
	
		if ($current_country):
	
			$current_countries = explode(',', $current_country);
	
		endif;
		
		return new ViewModel(array(
				'bannerid' => $id,
				'bannerpreviewid' => $banner_preview_id,
				'campaignid' => $campaign_id,
				'campaignpreviewid' => $campaign_preview_id,
				'ispreview' => $is_preview == true ? '1' : '0',
				'countrylist' => \util\Countries::$allcountries,
				'current_states' => $current_states,
				'current_countries' => $current_countries,
				'foldpos_options' => \util\DeliveryFilterOptions::$foldpos_options,
				'current_foldpos' => $current_foldpos,
				'geocity_option' => $geocity_option,
				'pmpenable_options' => \util\DeliveryFilterOptions::$pmpenable_options,
				'current_pmpenable' => $current_pmpenable,
				'secure_options' => \util\DeliveryFilterOptions::$secure_options,
				'current_secure' => $current_secure,
				'optout_options' => \util\DeliveryFilterOptions::$optout_options,
				'current_optout' => $current_optout,
				'vertical_options' => \util\DeliveryFilterOptions::$vertical_options,
				'current_verticals' => $current_verticals,
				'bread_crumb_info' => $this->getBreadCrumbInfoFromBanner($id, $banner_preview_id, $is_preview),
				'user_id_list' => $this->user_id_list_demand_customer,
				'center_class' => 'centerj',
				'user_identity' => $this->identity(),
				'true_user_name' => $this->auth->getUserName(),
				'header_title' => 'Edit Delivery Filter',
				'is_super_admin' => $this->is_super_admin,
				'effective_id' => $this->auth->getEffectiveIdentityID(),
				'impersonate_id' => $this->ImpersonateID,
				
				'fold_pos' => \util\BannerOptions::$fold_pos,
				'linearity' => \util\BannerOptions::$linearity,
				'start_delay' => \util\BannerOptions::$start_delay,
				'playback_methods' => \util\BannerOptions::$playback_methods,
				'delivery_methods' => \util\BannerOptions::$delivery_methods,
				'apis_supported' => \util\BannerOptions::$apis_supported,
				'protocols' => \util\BannerOptions::$protocols,
				'mimes' => \util\BannerOptions::$mimes,
				
				'MinHeight' => '',
				'MinWidth' => '',
				
				'current_mimes' => $current_mimes,
				
				'MinDuration' => $current_min_duration,
				'MaxDuration' => $current_max_duration,
				
				'current_apis_supported' => $current_apis_supported,
				'current_protocols' => $current_protocols,
				'current_delivery_methods' => $current_delivery_methods,
				'current_playback_methods' => $current_playback_methods,
				'current_start_delay' => $current_start_delay,
				'current_linearity' => $current_linearity,
				'current_fold_pos' => $current_foldpos,
				
				'MinHeight' => $current_min_height,
				'MinWidth' => $current_min_width,
				
		));
	}
	
	/**
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
	public function deliveryfilterAction() {
		$id = $this->getEvent()->getRouteMatch()->getParam('param1');
			if ($id == null):
				die("Invalid Banner ID");
			endif;

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		$is_preview = $this->getRequest()->getQuery('ispreview');

		// verify
			if ($is_preview == "true"):
				$is_preview = \transformation\TransformPreview::doesPreviewBannerExist($id, $this->auth);
			endif;

		$banner_preview_id = "";
		$campaign_id = "";
		$campaign_preview_id = "";

			if ($is_preview == true):
				// ACL PREVIEW PERMISSIONS CHECK
				transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($id, $this->auth, $this->config_handle);
	
				$InsertionOrderLineItemRestrictionsPreviewFactory = \_factory\InsertionOrderLineItemRestrictionsPreview::get_instance();
				$params = array();
				$params["InsertionOrderLineItemPreviewID"] = $id;
				$banner_preview_id = $id;
				$id = "";
				$InsertionOrderLineItemRestrictions = $InsertionOrderLineItemRestrictionsPreviewFactory->get_row($params);
	
				$InsertionOrderLineItemPreviewFactory = \_factory\InsertionOrderLineItemPreview::get_instance();
				$params = array();
				$params["InsertionOrderLineItemPreviewID"] = $banner_preview_id;
				$InsertionOrderLineItemPreview = $InsertionOrderLineItemPreviewFactory->get_row($params);
				$campaign_preview_id = $InsertionOrderLineItemPreview->InsertionOrderPreviewID;
	
			else:
				// ACL PERMISSIONS CHECK
				transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItem($id, $this->auth, $this->config_handle);
	
				$InsertionOrderLineItemRestrictionsFactory = \_factory\InsertionOrderLineItemRestrictions::get_instance();
				$params = array();
				$params["InsertionOrderLineItemID"] = $id;
	
				$InsertionOrderLineItemRestrictions = $InsertionOrderLineItemRestrictionsFactory->get_row($params);
	
				$InsertionOrderLineItemFactory = \_factory\InsertionOrderLineItem::get_instance();
				$params = array();
				$params["InsertionOrderLineItemID"] = $id;
				$InsertionOrderLineItem = $InsertionOrderLineItemFactory->get_row($params);
				$campaign_id = $InsertionOrderLineItem->InsertionOrderID;
			endif;

		$current_states = "";
		$current_country = "";
		$current_foldpos = "";
		$frequency_option = "";
		$geocity_option = "";
		$adpositionminleft_option = "";
		$adpositionmaxleft_option = "";
		$adpositionmintop_option = "";
		$adpositionmaxtop_option = "";
		$current_timezone = "";
		$current_adtagtype = "";
		$current_iniframe = "";
		$current_inmultiplenestediframes = "";
		$minscreenresolutionwidth_option = "";
		$maxscreenresolutionwidth_option = "";
		$minscreenresolutionheight_option = "";
		$maxscreenresolutionheight_option = "";
		$httplanguage_option = "";
		$browseruseragentgrep_option = "";
		$cookiegrep_option = "";
		$current_pmpenable = "";
		$current_secure = "";
		$current_optout = "";
		$current_vertical = array();


		if ($InsertionOrderLineItemRestrictions != null):

    		$current_states = $InsertionOrderLineItemRestrictions->GeoState == null ? "" : $InsertionOrderLineItemRestrictions->GeoState;
    		$current_country = $InsertionOrderLineItemRestrictions->GeoCountry == null ? "" : $InsertionOrderLineItemRestrictions->GeoCountry;
    		$current_foldpos = $InsertionOrderLineItemRestrictions->FoldPos == null ? "" : $InsertionOrderLineItemRestrictions->FoldPos;
    		$frequency_option = $InsertionOrderLineItemRestrictions->Freq == null ? "" : $InsertionOrderLineItemRestrictions->Freq;
    		$geocity_option = $InsertionOrderLineItemRestrictions->GeoCity == null ? "" : $InsertionOrderLineItemRestrictions->GeoCity;
    		$adpositionminleft_option = $InsertionOrderLineItemRestrictions->AdPositionMinLeft == null ? "" : $InsertionOrderLineItemRestrictions->AdPositionMinLeft;
    		$adpositionmaxleft_option = $InsertionOrderLineItemRestrictions->AdPositionMaxLeft == null ? "" : $InsertionOrderLineItemRestrictions->AdPositionMaxLeft;
    		$adpositionmintop_option = $InsertionOrderLineItemRestrictions->AdPositionMinTop == null ? "" : $InsertionOrderLineItemRestrictions->AdPositionMinTop;
    		$adpositionmaxtop_option = $InsertionOrderLineItemRestrictions->AdPositionMaxTop == null ? "" : $InsertionOrderLineItemRestrictions->AdPositionMaxTop;
    		$current_timezone = $InsertionOrderLineItemRestrictions->Timezone == null ? "" : $InsertionOrderLineItemRestrictions->Timezone;
    		$current_adtagtype = $InsertionOrderLineItemRestrictions->AdTagType == null ? "" : $InsertionOrderLineItemRestrictions->AdTagType;
    		$current_iniframe = $InsertionOrderLineItemRestrictions->InIframe == null ? "" : $InsertionOrderLineItemRestrictions->InIframe;
    		$current_inmultiplenestediframes = $InsertionOrderLineItemRestrictions->InMultipleNestedIframes == null ? "" : $InsertionOrderLineItemRestrictions->InMultipleNestedIframes;
    		$minscreenresolutionwidth_option = $InsertionOrderLineItemRestrictions->MinScreenResolutionWidth == null ? "" : $InsertionOrderLineItemRestrictions->MinScreenResolutionWidth;
    		$maxscreenresolutionwidth_option = $InsertionOrderLineItemRestrictions->MaxScreenResolutionWidth == null ? "" : $InsertionOrderLineItemRestrictions->MaxScreenResolutionWidth;
    		$minscreenresolutionheight_option = $InsertionOrderLineItemRestrictions->MinScreenResolutionHeight == null ? "" : $InsertionOrderLineItemRestrictions->MinScreenResolutionHeight;
    		$maxscreenresolutionheight_option = $InsertionOrderLineItemRestrictions->MaxScreenResolutionHeight == null ? "" : $InsertionOrderLineItemRestrictions->MaxScreenResolutionHeight;
    		$httplanguage_option = $InsertionOrderLineItemRestrictions->HttpLanguage == null ? "" : $InsertionOrderLineItemRestrictions->HttpLanguage;
    		$browseruseragentgrep_option = $InsertionOrderLineItemRestrictions->BrowserUserAgentGrep == null ? "" : $InsertionOrderLineItemRestrictions->BrowserUserAgentGrep;
    		$cookiegrep_option = $InsertionOrderLineItemRestrictions->CookieGrep == null ? "" : $InsertionOrderLineItemRestrictions->CookieGrep;
    		$current_pmpenable = $InsertionOrderLineItemRestrictions->PmpEnable == null ? "" : $InsertionOrderLineItemRestrictions->PmpEnable;
    		$current_secure = $InsertionOrderLineItemRestrictions->Secure == null ? "" : $InsertionOrderLineItemRestrictions->Secure;
    		$current_optout = $InsertionOrderLineItemRestrictions->Optout == null ? "" : $InsertionOrderLineItemRestrictions->Optout;
    		$current_vertical = $InsertionOrderLineItemRestrictions->Vertical == null ? "" : $InsertionOrderLineItemRestrictions->Vertical;

		endif;

		$current_verticals = array();

		if ($current_vertical):

            $current_verticals = explode(',', $current_vertical);

		endif;

		$current_countries = array();

		if ($current_country):

		  $current_countries = explode(',', $current_country);

		endif;

		$current_timezones = array();

		if ($current_timezone):

		  $current_timezones = explode(',', $current_timezone);

		endif;

		//var_dump($current_country);
		//exit;

		return new ViewModel(array(
				'bannerid' => $id,
				'bannerpreviewid' => $banner_preview_id,
				'campaignid' => $campaign_id,
				'campaignpreviewid' => $campaign_preview_id,
				'ispreview' => $is_preview == true ? '1' : '0',
		        'countrylist' => \util\Countries::$allcountries,
		        'current_states' => $current_states,
		        'current_countries' => $current_countries,
		        'foldpos_options' => \util\DeliveryFilterOptions::$foldpos_options,
		        'current_foldpos' => $current_foldpos,
		        'frequency_option' => $frequency_option,
    		    'geocity_option' => $geocity_option,
    		    'adpositionminleft_option' => $adpositionminleft_option,
    		    'adpositionmaxleft_option' => $adpositionmaxleft_option,
    		    'adpositionmintop_option' => $adpositionmintop_option,
    		    'adpositionmaxtop_option' => $adpositionmaxtop_option,
		        'adtagtype_options' => \util\DeliveryFilterOptions::$adtagtype_options,
		        'current_adtagtype' => $current_adtagtype,
		        'timezone_options' => \util\DeliveryFilterOptions::$timezone_options,
		        'current_timezones' => $current_timezones,
    		    'iniframe_options' => \util\DeliveryFilterOptions::$iniframe_options,
    		    'current_iniframe' => $current_iniframe,
    		    'inmultiplenestediframes_options' => \util\DeliveryFilterOptions::$inmultiplenestediframes_options,
    		    'current_inmultiplenestediframes' => $current_inmultiplenestediframes,
    		    'minscreenresolutionwidth_option' => $minscreenresolutionwidth_option,
    		    'maxscreenresolutionwidth_option' => $maxscreenresolutionwidth_option,
    		    'minscreenresolutionheight_option' => $minscreenresolutionheight_option,
    		    'maxscreenresolutionheight_option' => $maxscreenresolutionheight_option,
		        'httplanguage_option' => $httplanguage_option,
		        'browseruseragentgrep_option' => $browseruseragentgrep_option,
		        'cookiegrep_option' => $cookiegrep_option,
    		    'pmpenable_options' => \util\DeliveryFilterOptions::$pmpenable_options,
    		    'current_pmpenable' => $current_pmpenable,
    		    'secure_options' => \util\DeliveryFilterOptions::$secure_options,
    		    'current_secure' => $current_secure,
    		    'optout_options' => \util\DeliveryFilterOptions::$optout_options,
    		    'current_optout' => $current_optout,
    		    'vertical_options' => \util\DeliveryFilterOptions::$vertical_options,
    		    'current_verticals' => $current_verticals,
				'bread_crumb_info' => $this->getBreadCrumbInfoFromBanner($id, $banner_preview_id, $is_preview),
				'user_id_list' => $this->user_id_list_demand_customer,
    			'center_class' => 'centerj',
    			'user_identity' => $this->identity(),
    			'true_user_name' => $this->auth->getUserName(),
				'header_title' => 'Edit Delivery Filter',
				'is_super_admin' => $this->is_super_admin,
				'effective_id' => $this->auth->getEffectiveIdentityID(),
				'impersonate_id' => $this->ImpersonateID
		));
	}

	/*
	 * END NGINAD InsertionOrderLineItemRestrictions Actions
	*/

	/*
	 * BEGIN NGINAD InsertionOrderLineItemDomainExclusiveInclusion Actions
	*/

	/**
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
	public function viewexclusiveinclusionAction() {
		$id = $this->getEvent()->getRouteMatch()->getParam('param1');
		if ($id == null):
			die("Invalid Banner ID");
		endif;

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		$is_preview = $this->getRequest()->getQuery('ispreview');

		// verify
		if ($is_preview == "true"):
			$is_preview = \transformation\TransformPreview::doesPreviewBannerExist($id, $this->auth);
		endif;

		$banner_preview_id = "";
		$campaign_id = "";
		$campaign_preview_id = "";

		if ($is_preview == true):
			// ACL PREVIEW PERMISSIONS CHECK
			transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($id, $this->auth, $this->config_handle);

			$InsertionOrderLineItemDomainExclusiveInclusionPreviewFactory = \_factory\InsertionOrderLineItemDomainExclusiveInclusionPreview::get_instance();
			$params = array();
			$params["InsertionOrderLineItemPreviewID"] = $id;
			$banner_preview_id = $id;
			$id = "";
			$rtb_domain_exclusive_inclusions = $InsertionOrderLineItemDomainExclusiveInclusionPreviewFactory->get($params);

			$InsertionOrderLineItemPreviewFactory = \_factory\InsertionOrderLineItemPreview::get_instance();
			$params = array();
			$params["InsertionOrderLineItemPreviewID"] = $banner_preview_id;

			$InsertionOrderLineItemPreview = $InsertionOrderLineItemPreviewFactory->get_row($params);
			$campaign_preview_id = $InsertionOrderLineItemPreview->InsertionOrderPreviewID;

		else:
			// ACL PERMISSIONS CHECK
			transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItem($id, $this->auth, $this->config_handle);

			$InsertionOrderLineItemDomainExclusiveInclusionFactory = \_factory\InsertionOrderLineItemDomainExclusiveInclusion::get_instance();
			$params = array();
			$params["InsertionOrderLineItemID"] = $id;
			$rtb_domain_exclusive_inclusions = $InsertionOrderLineItemDomainExclusiveInclusionFactory->get($params);

			$InsertionOrderLineItemFactory = \_factory\InsertionOrderLineItem::get_instance();
			$params = array();
			$params["InsertionOrderLineItemID"] = $id;

			$InsertionOrderLineItem = $InsertionOrderLineItemFactory->get_row($params);
			$campaign_id = $InsertionOrderLineItem->InsertionOrderID;

		endif;

		if ($is_preview == true):
			$rtb_banner_id = $banner_preview_id;
			$ad_campaign_id = $campaign_preview_id;
		else:
			$rtb_banner_id = $id;
			$ad_campaign_id = $campaign_id;
		endif;
		
		return new ViewModel(array(
				'ispreview'	  => $is_preview == true ? '1' : '0',
				'rtb_domain_exclusive_inclusions' => $rtb_domain_exclusive_inclusions,
				'banner_id' => $id,
				'banner_preview_id' => $banner_preview_id,
				'campaign_id' => $campaign_id,
				'campaign_preview_id' => $campaign_preview_id,
				'bread_crumb_info' => $this->getBreadCrumbInfoFromBanner($id, $banner_preview_id, $is_preview),
				'user_id_list' => $this->user_id_list_demand_customer,
    			'center_class' => 'centerj',
				'user_identity' => $this->identity(),
				'true_user_name' => $this->auth->getUserName(),
				'header_title' => '<a href="/demand/createexclusiveinclusion/' . $rtb_banner_id . $this->preview_query . '">Create Domain Exclusive Inclusion</a>',
				'is_super_admin' => $this->is_super_admin,
				'effective_id' => $this->auth->getEffectiveIdentityID(),
				'impersonate_id' => $this->ImpersonateID
		));
	}

	/**
	 * 
	 * @return Ambigous <\Zend\View\Model\ViewModel, \Zend\View\Model\ViewModel>
	 */
	public function deleteexclusiveinclusionAction() {
		
		$error_msg = null;
		$success = true;

		$id = $this->getEvent()->getRouteMatch()->getParam('param1');
		if ($id == null):
			//die("Invalid DomainExclusiveInclusion ID");
			$error_msg = "Invalid DomainExclusiveInclusion ID";
		    $success = false;
		    $data = array(
	         'success' => $success,
	         'data' => array('error_msg' => $error_msg)
   		   );
   		 
          return $this->getResponse()->setContent(json_encode($data));
		endif;

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		$is_preview = $this->getRequest()->getQuery('ispreview');

		$exclusiveinclusion_preview_id = null;

		$InsertionOrderLineItemDomainExclusiveInclusionPreviewFactory = \_factory\InsertionOrderLineItemDomainExclusiveInclusionPreview::get_instance();

		// verify
		if ($is_preview != "true"):

			$InsertionOrderLineItemDomainExclusiveInclusionFactory = \_factory\InsertionOrderLineItemDomainExclusiveInclusion::get_instance();
			$params = array();
			$params["InsertionOrderLineItemDomainExclusiveInclusionID"] = $id;
			$rtb_domain_exclusive_inclusion = $InsertionOrderLineItemDomainExclusiveInclusionFactory->get_row($params);

			if ($rtb_domain_exclusive_inclusion == null):
				//die("Invalid InsertionOrderLineItemDomainExclusiveInclusion ID");
				$error_msg = "Invalid InsertionOrderLineItemDomainExclusiveInclusion ID";
			    $success = false;
			    $data = array(
		         'success' => $success,
		         'data' => array('error_msg' => $error_msg)
	   		   );
   		 
          		return $this->getResponse()->setContent(json_encode($data));
			endif;

			$banner_id = $rtb_domain_exclusive_inclusion->InsertionOrderLineItemID;

			// ACL PERMISSIONS CHECK
			$response = transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItem($banner_id, $this->auth, $this->config_handle);
			
			if(array_key_exists("error", $response) > 0):
				$success = false;
				$data = array(
			       'success' => $success,
			       'data' => array('error_msg' => $response['error'])
		   		);
		   		
		   	   return $this->getResponse()->setContent(json_encode($data));
			endif;
			
			/*
			 * THIS METHOD CHECKS IF THERE IS AN EXISTING PREVIEW MODE CAMPAIGN
			* IF NOT, IT CHECKS THE ACL PERMISSIONS ON THE PRODUCTION BANNER/CAMPAIGN REFERENCED
			* THEN IT CREATES A PREVIEW VERSION OF THE AD CAMPAIGN
			*/

			$update_data = array('type'=>'InsertionOrderLineItemDomainExclusiveInclusionID', 'id'=>$id);

			$return_val = \transformation\TransformPreview::previewCheckBannerID($banner_id, $this->auth, $this->config_handle, $this->getServiceLocator()->get('mail.transport'), $update_data);

			if ($return_val !== null && array_key_exists("error", $return_val)):

				$success = false;
				$data = array(
			       'success' => $success,
			       'data' => array('error_msg' => $return_val['error'])
		   		);
   		
		   	   return $this->getResponse()->setContent(json_encode($data));
			endif;
			
			if ($return_val !== null):
				$banner_preview_id 	= $return_val["InsertionOrderLineItemPreviewID"];
				$exclusiveinclusion_preview_id = $return_val["InsertionOrderLineItemDomainExclusiveInclusionPreviewID"];
			endif;

		else:

			$params = array();
			$params["InsertionOrderLineItemDomainExclusiveInclusionPreviewID"] = $id;
			$rtb_domain_exclusive_inclusion_preview = $InsertionOrderLineItemDomainExclusiveInclusionPreviewFactory->get_row($params);

			if ($rtb_domain_exclusive_inclusion_preview == null):
				//die("Invalid InsertionOrderLineItemDomainExclusiveInclusionPreview ID");
				$error_msg = "Invalid InsertionOrderLineItemDomainExclusiveInclusionPreview ID";
			    $success = false;
			    $data = array(
		         'success' => $success,
		         'data' => array('error_msg' => $error_msg)
	   		   );
   		 
          		return $this->getResponse()->setContent(json_encode($data));
			endif;

			$banner_preview_id = $rtb_domain_exclusive_inclusion_preview->InsertionOrderLineItemPreviewID;
			$exclusiveinclusion_preview_id = $rtb_domain_exclusive_inclusion_preview->InsertionOrderLineItemDomainExclusiveInclusionPreviewID;

			// ACL PREVIEW PERMISSIONS CHECK
			$response = transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($banner_preview_id, $this->auth, $this->config_handle);
			
			if(array_key_exists("error", $response) > 0):
				$success = false;
				$data = array(
			       'success' => $success,
			       'data' => array('error_msg' => $response['error'])
		   		);
		   		
		   	   return $this->getResponse()->setContent(json_encode($data));
			endif;

		endif;

		$InsertionOrderLineItemDomainExclusiveInclusionPreviewFactory->deleteInsertionOrderLineItemDomainExclusiveInclusionPreview($exclusiveinclusion_preview_id);


		  $data = array(
		     'success' => $success,
		  	 'location' => '/demand/viewexclusiveinclusion/',
		  	 'previewid' => $banner_preview_id,
		     'data' => array('error_msg' => $error_msg)
	   	  );
   		 
      	return $this->getResponse()->setContent(json_encode($data));
	}

	/**
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
	public function createexclusiveinclusionAction() {
		$id = $this->getEvent()->getRouteMatch()->getParam('param1');
		if ($id == null):
			die("Invalid Banner ID");
		endif;

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		$is_preview = $this->getRequest()->getQuery('ispreview');

		// verify
		if ($is_preview == "true"):
			$is_preview = \transformation\TransformPreview::doesPreviewBannerExist($id, $this->auth);
		endif;

		$banner_preview_id = "";
		$campaign_preview_id = "";
		$campaign_id = "";

		if ($is_preview == "true"):
			// ACL PERMISSIONS CHECK
			transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($id, $this->auth, $this->config_handle);
			$banner_preview_id = $id;
			$id = "";

			$InsertionOrderLineItemPreviewFactory = \_factory\InsertionOrderLineItemPreview::get_instance();
			$params = array();
			$params["InsertionOrderLineItemPreviewID"] = $banner_preview_id;
			$InsertionOrderLineItemPreview = $InsertionOrderLineItemPreviewFactory->get_row($params);
			$campaign_preview_id = $InsertionOrderLineItemPreview->InsertionOrderPreviewID;

		else:
			// ACL PERMISSIONS CHECK
			transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItem($id, $this->auth, $this->config_handle);

			$InsertionOrderLineItemFactory = \_factory\InsertionOrderLineItem::get_instance();
			$params = array();
			$params["InsertionOrderLineItemID"] = $id;
			$InsertionOrderLineItem = $InsertionOrderLineItemFactory->get_row($params);
			$campaign_id = $InsertionOrderLineItem->InsertionOrderID;

		endif;

		return new ViewModel(array(
				'ispreview' => $is_preview == true ? '1' : '0',
				'bannerid' => $id,
				'bannerpreviewid' => $banner_preview_id,
				'campaignid' => $campaign_id,
				'campaignpreviewid' => $campaign_preview_id,
				'bread_crumb_info' => $this->getBreadCrumbInfoFromBanner($id, $banner_preview_id, $is_preview),
				'user_id_list' => $this->user_id_list_demand_customer,
    			'center_class' => 'centerj',
    			'user_identity' => $this->identity(),
				'true_user_name' => $this->auth->getUserName(),
				'header_title' => 'Create Domain Exclusive Inclusion',
				'is_super_admin' => $this->is_super_admin,
				'effective_id' => $this->auth->getEffectiveIdentityID(),
				'impersonate_id' => $this->ImpersonateID
		));
	}

	/**
	 * 
	 * @return Ambigous <\Zend\View\Model\ViewModel, \Zend\View\Model\ViewModel>
	 */
	public function newexclusiveinclusionAction() {

		$needed_input = array(
				'inclusiontype',
				'domainname'
		);

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		$this->validateInput($needed_input);

		$bannerid 				= $this->getRequest()->getPost('bannerid');
		$banner_preview_id 		= $this->getRequest()->getPost('bannerpreviewid');
		$ispreview 				= $this->getRequest()->getPost('ispreview');

		if ($ispreview != true):
			/*
			 * THIS METHOD CHECKS IF THERE IS AN EXISTING PREVIEW MODE CAMPAIGN
			* IF NOT, IT CHECKS THE ACL PERMISSIONS ON THE PRODUCTION BANNER/CAMPAIGN REFERENCED
			* THEN IT CREATES A PREVIEW VERSION OF THE AD CAMPAIGN
			*/
			$update_data = array('type'=>'InsertionOrderLineItemID', 'id'=>$bannerid);
			$return_val = \transformation\TransformPreview::previewCheckBannerID($bannerid, $this->auth, $this->config_handle, $this->getServiceLocator()->get('mail.transport'), $update_data);

			if ($return_val !== null):
				$banner_preview_id = $return_val["InsertionOrderLineItemPreviewID"];
			endif;

		endif;

		// ACL PREVIEW PERMISSIONS CHECK
		transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($banner_preview_id, $this->auth, $this->config_handle);

		$inclusiontype = $this->getRequest()->getPost('inclusiontype');
		$domainname = $this->getRequest()->getPost('domainname');

		$BannerDomainExclusiveInclusionPreview = new \model\InsertionOrderLineItemDomainExclusiveInclusionPreview();
		$BannerDomainExclusiveInclusionPreview->InsertionOrderLineItemPreviewID           = $banner_preview_id;
		$BannerDomainExclusiveInclusionPreview->InclusionType             = $inclusiontype;
		$BannerDomainExclusiveInclusionPreview->DomainName                = $domainname;
		$BannerDomainExclusiveInclusionPreview->DateCreated               = date("Y-m-d H:i:s");
		$BannerDomainExclusiveInclusionPreview->DateUpdated               = date("Y-m-d H:i:s");

		$InsertionOrderLineItemDomainExclusiveInclusionPreviewFactory = \_factory\InsertionOrderLineItemDomainExclusiveInclusionPreview::get_instance();
		$InsertionOrderLineItemDomainExclusiveInclusionPreviewFactory->saveInsertionOrderLineItemDomainExclusiveInclusionPreview($BannerDomainExclusiveInclusionPreview);

		$refresh_url = "/demand/viewexclusiveinclusion/" . $banner_preview_id . "?ispreview=true";
		$viewModel = new ViewModel(array('refresh_url' => $refresh_url));

		return $viewModel->setTemplate('dashboard-manager/demand/interstitial.phtml');

	}

	/*
	 * END NGINAD InsertionOrderLineItemDomainExclusiveInclusion Actions
	*/

	/*
	 * BEGIN NGINAD InsertionOrderLineItemDomainExclusion Actions
	*/


	/**
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
	public function viewdomainexclusionAction() {
		$id = $this->getEvent()->getRouteMatch()->getParam('param1');
		if ($id == null):
			die("Invalid Banner ID");
		endif;

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		$is_preview = $this->getRequest()->getQuery('ispreview');

		// verify
		if ($is_preview == "true"):
			$is_preview = \transformation\TransformPreview::doesPreviewBannerExist($id, $this->auth);
		endif;

		$banner_preview_id = "";
		$campaign_id = "";
		$campaign_preview_id = "";

		if ($is_preview == true):
			// ACL PREVIEW PERMISSIONS CHECK
			transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($id, $this->auth, $this->config_handle);

			$InsertionOrderLineItemDomainExclusionPreviewFactory = \_factory\InsertionOrderLineItemDomainExclusionPreview::get_instance();
			$params = array();
			$params["InsertionOrderLineItemPreviewID"] = $id;
			$banner_preview_id = $id;
			$id = "";
			$rtb_domain_exclusions = $InsertionOrderLineItemDomainExclusionPreviewFactory->get($params);

			$InsertionOrderLineItemPreviewFactory = \_factory\InsertionOrderLineItemPreview::get_instance();
			$params = array();
			$params["InsertionOrderLineItemPreviewID"] = $banner_preview_id;

			$InsertionOrderLineItemPreview = $InsertionOrderLineItemPreviewFactory->get_row($params);
			$campaign_preview_id = $InsertionOrderLineItemPreview->InsertionOrderPreviewID;

		else:
			// ACL PERMISSIONS CHECK
			transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItem($id, $this->auth, $this->config_handle);

			$InsertionOrderLineItemDomainExclusionFactory = \_factory\InsertionOrderLineItemDomainExclusion::get_instance();
			$params = array();
			$params["InsertionOrderLineItemID"] = $id;
			$rtb_domain_exclusions = $InsertionOrderLineItemDomainExclusionFactory->get($params);

			$InsertionOrderLineItemFactory = \_factory\InsertionOrderLineItem::get_instance();
			$params = array();
			$params["InsertionOrderLineItemID"] = $id;

			$InsertionOrderLineItem = $InsertionOrderLineItemFactory->get_row($params);
			$campaign_id = $InsertionOrderLineItem->InsertionOrderID;

		endif;

		if ($is_preview == true):
			$rtb_banner_id = $banner_preview_id;
			$ad_campaign_id = $campaign_preview_id;
		else:
			$rtb_banner_id = $id;
			$ad_campaign_id = $campaign_id;
		endif;
		
		return new ViewModel(array(
				'ispreview'	  => $is_preview == true ? '1' : '0',
				'rtb_domain_exclusions' => $rtb_domain_exclusions,
				'banner_id' => $id,
				'banner_preview_id' => $banner_preview_id,
				'campaign_id' => $campaign_id,
				'campaign_preview_id' => $campaign_preview_id,
				'bread_crumb_info' => $this->getBreadCrumbInfoFromBanner($id, $banner_preview_id, $is_preview),
				'user_id_list' => $this->user_id_list_demand_customer,
    			'center_class' => 'centerj',
    			'user_identity' => $this->identity(),
				'true_user_name' => $this->auth->getUserName(),
				'header_title' => '<a href="/demand/createdomainexclusion/' . $rtb_banner_id . $this->preview_query . '">Create Domain Exclusion</a>',
				'is_super_admin' => $this->is_super_admin,
				'effective_id' => $this->auth->getEffectiveIdentityID(),
				'impersonate_id' => $this->ImpersonateID
		));
	}

	/**
	 * 
	 * @return Ambigous <\Zend\View\Model\ViewModel, \Zend\View\Model\ViewModel>
	 */
	public function deletedomainexclusionAction() {
		
		$error_msg = null;
		$success = true;
		$id = $this->getEvent()->getRouteMatch()->getParam('param1');
		if ($id == null):
			//die("Invalid DomainExclusion ID");
			$error_msg = "Invalid Domain Exclusion ID";
		    $success = false;
		    $data = array(
	         'success' => $success,
	         'data' => array('error_msg' => $error_msg)
   		   );
   		 
           return $this->getResponse()->setContent(json_encode($data));
		endif;

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		$is_preview = $this->getRequest()->getQuery('ispreview');

		$exclusion_preview_id = null;

		$InsertionOrderLineItemDomainExclusionPreviewFactory = \_factory\InsertionOrderLineItemDomainExclusionPreview::get_instance();

		// verify
		if ($is_preview != "true"):

			$InsertionOrderLineItemDomainExclusionFactory = \_factory\InsertionOrderLineItemDomainExclusion::get_instance();
			$params = array();
			$params["InsertionOrderLineItemDomainExclusionID"] = $id;
			$rtb_domain_exclusion = $InsertionOrderLineItemDomainExclusionFactory->get_row($params);

			if ($rtb_domain_exclusion == null):
				//die("Invalid InsertionOrderLineItemDomainExclusion ID");
				$error_msg = "Invalid InsertionOrderLineItem Domain Exclusion ID";
			    $success = false;
			    $data = array(
		         'success' => $success,
		         'data' => array('error_msg' => $error_msg)
	   		   );
	   		 
	           return $this->getResponse()->setContent(json_encode($data));
				
			endif;

			$banner_id = $rtb_domain_exclusion->InsertionOrderLineItemID;

			// ACL PERMISSIONS CHECK
			//transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItem($banner_id, $auth, $config);
			$response = transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItem($banner_id, $this->auth, $this->config_handle);
			
			if(array_key_exists("error", $response) > 0):
				$success = false;
				$data = array(
			       'success' => $success,
			       'data' => array('error_msg' => $response['error'])
		   		);
		   		
		   	   return $this->getResponse()->setContent(json_encode($data));
			endif;
			
			/*
			 * THIS METHOD CHECKS IF THERE IS AN EXISTING PREVIEW MODE CAMPAIGN
			* IF NOT, IT CHECKS THE ACL PERMISSIONS ON THE PRODUCTION BANNER/CAMPAIGN REFERENCED
			* THEN IT CREATES A PREVIEW VERSION OF THE AD CAMPAIGN
			*/

			$update_data = array('type'=>'InsertionOrderLineItemDomainExclusionID', 'id'=>$id);

			$return_val = \transformation\TransformPreview::previewCheckBannerID($banner_id, $this->auth, $this->config_handle, $this->getServiceLocator()->get('mail.transport'), $update_data);

			if ($return_val !== null && array_key_exists("error", $return_val)):

				$success = false;
				$data = array(
			       'success' => $success,
			       'data' => array('error_msg' => $return_val['error'])
		   		);
   		
		   	   return $this->getResponse()->setContent(json_encode($data));
			endif;
			
			if ($return_val !== null):
				$banner_preview_id 	= $return_val["InsertionOrderLineItemPreviewID"];
				$exclusion_preview_id = $return_val["InsertionOrderLineItemDomainExclusionPreviewID"];
			endif;

		else:

			$params = array();
			$params["InsertionOrderLineItemDomainExclusionPreviewID"] = $id;
			$rtb_domain_exclusion_preview = $InsertionOrderLineItemDomainExclusionPreviewFactory->get_row($params);

			if ($rtb_domain_exclusion_preview == null):
				//die("Invalid InsertionOrderLineItemDomainExclusionPreview ID");
				$error_msg = "Invalid InsertionOrderLineItem Domain Exclusion Preview ID";
			    $success = false;
			    $data = array(
		         'success' => $success,
		         'data' => array('error_msg' => $error_msg)
	   		   );
	   		 
	           return $this->getResponse()->setContent(json_encode($data));
			endif;

			$banner_preview_id = $rtb_domain_exclusion_preview->InsertionOrderLineItemPreviewID;
			$exclusion_preview_id = $rtb_domain_exclusion_preview->InsertionOrderLineItemDomainExclusionPreviewID;

			// ACL PREVIEW PERMISSIONS CHECK
			//transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($banner_preview_id, $auth, $config);
			$response = transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($banner_preview_id, $this->auth, $this->config_handle);
			
			
			if(array_key_exists("error", $response) > 0):
				$success = false;
				$data = array(
			       'success' => $success,
			       'data' => array('error_msg' => $response['error'])
		   		);
		   		
		   	   return $this->getResponse()->setContent(json_encode($data));
			endif;

		endif;

		$InsertionOrderLineItemDomainExclusionPreviewFactory->deleteInsertionOrderLineItemDomainExclusionPreview($exclusion_preview_id);

		$data = array(
		         'success' => $success,
				 'location' => '/demand/viewdomainexclusion/',
				 'previewid' => $banner_preview_id,
		         'data' => array('error_msg' => $error_msg)
	   		   );
	   		 
	    return $this->getResponse()->setContent(json_encode($data));
	    
	}

	/**
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
	public function createdomainexclusionAction() {
		$id = $this->getEvent()->getRouteMatch()->getParam('param1');
		if ($id == null):
			die("Invalid Banner ID");
		endif;

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		$is_preview = $this->getRequest()->getQuery('ispreview');

		// verify
		if ($is_preview == "true"):
			$is_preview = \transformation\TransformPreview::doesPreviewBannerExist($id, $this->auth);
		endif;

		$banner_preview_id = "";
		$campaign_preview_id = "";
		$campaign_id = "";

		if ($is_preview == "true"):
			// ACL PERMISSIONS CHECK
			transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($id, $this->auth, $this->config_handle);
			$banner_preview_id = $id;
			$id = "";

			$InsertionOrderLineItemPreviewFactory = \_factory\InsertionOrderLineItemPreview::get_instance();
			$params = array();
			$params["InsertionOrderLineItemPreviewID"] = $banner_preview_id;
			$InsertionOrderLineItemPreview = $InsertionOrderLineItemPreviewFactory->get_row($params);
			$campaign_preview_id = $InsertionOrderLineItemPreview->InsertionOrderPreviewID;

		else:
			// ACL PERMISSIONS CHECK
			transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItem($id, $this->auth, $this->config_handle);

			$InsertionOrderLineItemFactory = \_factory\InsertionOrderLineItem::get_instance();
			$params = array();
			$params["InsertionOrderLineItemID"] = $id;
			$InsertionOrderLineItem = $InsertionOrderLineItemFactory->get_row($params);
			$campaign_id = $InsertionOrderLineItem->InsertionOrderID;
		endif;

		return new ViewModel(array(
				'ispreview' => $is_preview == true ? '1' : '0',
				'bannerid' => $id,
				'bannerpreviewid' => $banner_preview_id,
				'campaignid' => $campaign_id,
				'campaignpreviewid' => $campaign_preview_id,
				'bread_crumb_info' => $this->getBreadCrumbInfoFromBanner($id, $banner_preview_id, $is_preview),
				'user_id_list' => $this->user_id_list_demand_customer,
    			'center_class' => 'centerj',
    			'user_identity' => $this->identity(),
				'true_user_name' => $this->auth->getUserName(),
				'header_title' => 'Create Domain Exclusion',
				'is_super_admin' => $this->is_super_admin,
				'effective_id' => $this->auth->getEffectiveIdentityID(),
				'impersonate_id' => $this->ImpersonateID
		));
	}

	/**
	 * 
	 * @return Ambigous <\Zend\View\Model\ViewModel, \Zend\View\Model\ViewModel>
	 */
	public function newdomainexclusionAction() {

		$needed_input = array(
				'exclusiontype',
				'domainname'
		);

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		$this->validateInput($needed_input);

		$bannerid 				= $this->getRequest()->getPost('bannerid');
		$banner_preview_id 		= $this->getRequest()->getPost('bannerpreviewid');
		$ispreview 				= $this->getRequest()->getPost('ispreview');

		if ($ispreview != true):
			/*
			 * THIS METHOD CHECKS IF THERE IS AN EXISTING PREVIEW MODE CAMPAIGN
			* IF NOT, IT CHECKS THE ACL PERMISSIONS ON THE PRODUCTION BANNER/CAMPAIGN REFERENCED
			* THEN IT CREATES A PREVIEW VERSION OF THE AD CAMPAIGN
			*/
			$update_data = array('type'=>'InsertionOrderLineItemID', 'id'=>$bannerid);
			$return_val = \transformation\TransformPreview::previewCheckBannerID($bannerid, $this->auth, $this->config_handle, $this->getServiceLocator()->get('mail.transport'), $update_data);

			if ($return_val !== null):
				$banner_preview_id = $return_val["InsertionOrderLineItemPreviewID"];
			endif;

		endif;

		// ACL PREVIEW PERMISSIONS CHECK
		transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($banner_preview_id, $this->auth, $this->config_handle);

		$exclusiontype = $this->getRequest()->getPost('exclusiontype');
		$domainname = $this->getRequest()->getPost('domainname');

		$BannerDomainExclusionPreview = new \model\InsertionOrderLineItemDomainExclusionPreview();
		$BannerDomainExclusionPreview->InsertionOrderLineItemPreviewID           = $banner_preview_id;
		$BannerDomainExclusionPreview->ExclusionType             = $exclusiontype;
		$BannerDomainExclusionPreview->DomainName                = $domainname;
		$BannerDomainExclusionPreview->DateCreated               = date("Y-m-d H:i:s");
		$BannerDomainExclusionPreview->DateUpdated               = date("Y-m-d H:i:s");

		$InsertionOrderLineItemDomainExclusionPreviewFactory = \_factory\InsertionOrderLineItemDomainExclusionPreview::get_instance();
		$InsertionOrderLineItemDomainExclusionPreviewFactory->saveInsertionOrderLineItemDomainExclusionPreview($BannerDomainExclusionPreview);

		$refresh_url = "/demand/viewdomainexclusion/" . $banner_preview_id . "?ispreview=true";
		$viewModel = new ViewModel(array('refresh_url' => $refresh_url));

		return $viewModel->setTemplate('dashboard-manager/demand/interstitial.phtml');

	}

	/*
	 * END NGINAD InsertionOrderLineItemDomainExclusion Actions
	*/

	/*
	 * BEGIN NGINAD InsertionOrderLineItem Actions
	*/

	/**
	 * 
	 * @return Ambigous <\Zend\View\Model\ViewModel, \Zend\View\Model\ViewModel>
	 */
	public function deletelineitemAction() {
		
		$error_msg = null;
		$success = true;
		$id = $this->getEvent()->getRouteMatch()->getParam('param1');
		if ($id == null):
		  //die("Invalid Banner ID");
		   $error_msg = "Invalid Banner ID";
		   $success = false;
		   $data = array(
	         'success' => $success,
	         'data' => array('error_msg' => $error_msg)
   		  );
   		 
          return $this->getResponse()->setContent(json_encode($data));
		endif;

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		$is_preview = $this->getRequest()->getQuery('ispreview');

		// verify
		if ($is_preview != "true"):

			/*
			 * THIS METHOD CHECKS IF THERE IS AN EXISTING PREVIEW MODE CAMPAIGN
			* IF NOT, IT CHECKS THE ACL PERMISSIONS ON THE PRODUCTION BANNER/CAMPAIGN REFERENCED
			* THEN IT CREATES A PREVIEW VERSION OF THE AD CAMPAIGN
			*/

			$update_data = array('type'=>'InsertionOrderLineItemID', 'id'=>$id);
			$return_val = \transformation\TransformPreview::previewCheckBannerID($id, $this->auth, $this->config_handle, $this->getServiceLocator()->get('mail.transport'), $update_data);

			if ($return_val !== null && array_key_exists("error", $return_val)):

				$success = false;
				$data = array(
			       'success' => $success,
			       'data' => array('error_msg' => $return_val['error'])
		   		);
   		
		   	   return $this->getResponse()->setContent(json_encode($data));
			endif;
			
			if ($return_val !== null):
				$id 	= $return_val["InsertionOrderLineItemPreviewID"];
			endif;
	   endif;

		// ACL PREVIEW PERMISSIONS CHECK
		//transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($id, $auth, $config);
		$response = transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($id, $this->auth, $this->config_handle);
		if(array_key_exists("error", $response) > 0):
			$success = false;
			$data = array(
		       'success' => $success,
		       'data' => array('error_msg' => $response['error'])
	   		);
	   		
	   	   return $this->getResponse()->setContent(json_encode($data));
		endif;

		$InsertionOrderLineItemPreviewFactory = \_factory\InsertionOrderLineItemPreview::get_instance();
		$params = array();
		$params["InsertionOrderLineItemPreviewID"] = $id;

		$InsertionOrderLineItemPreview = $InsertionOrderLineItemPreviewFactory->get_row($params);

		if ($InsertionOrderLineItemPreview == null):
		  //die("Invalid Banner ID");
		  $error_msg = "Invalid Banner ID";
		   $success = false;
		   $data = array(
	         'success' => $success,
	         'data' => array('error_msg' => $error_msg)
   		  );
   		 
          return $this->getResponse()->setContent(json_encode($data));
		endif;

		$campaign_preview_id = $InsertionOrderLineItemPreview->InsertionOrderPreviewID;

		$InsertionOrderLineItemPreviewFactory->deActivateInsertionOrderLineItemPreview($id);

		$data = array(
	        'success' => $success,
			'location' => '/demand/viewlineitem/',
			'previewid' => $campaign_preview_id,
	        'data' => array('error_msg' => $error_msg)
   		 );
   		 
        return $this->getResponse()->setContent(json_encode($data));
		
		/*$refresh_url = "/demand/viewlineitem/" . $campaign_preview_id . "?ispreview=true";
		$viewModel = new ViewModel(array('refresh_url' => $refresh_url));

		return $viewModel->setTemplate('dashboard-manager/demand/interstitial.phtml');*/

	}

	/**
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
	public function viewlineitemAction() {
	    $id = $this->getEvent()->getRouteMatch()->getParam('param1');
        if ($id == null):
            die("Invalid Campaign ID");
        endif;

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

        $is_preview = $this->getRequest()->getQuery('ispreview');
        $campaign_preview_id = "";

        // verify
		if ($is_preview == "true"):
			$is_preview = \transformation\TransformPreview::doesPreviewInsertionOrderExist($id, $this->auth);
		endif;

		if ($is_preview == true):
			// ACL PERMISSIONS CHECK
			transformation\CheckPermissions::checkEditPermissionInsertionOrderPreview($id, $this->auth, $this->config_handle);

			$InsertionOrderLineItemPreviewFactory = \_factory\InsertionOrderLineItemPreview::get_instance();
			$params = array();
			$params["InsertionOrderPreviewID"] = $id;
			$params["Active"] = 1;

			$rtb_banner_list = $InsertionOrderLineItemPreviewFactory->get($params);
			$campaign_preview_id = $id;
			$id = "";
		else:
			// ACL PERMISSIONS CHECK
			transformation\CheckPermissions::checkEditPermissionInsertionOrder($id, $this->auth, $this->config_handle);

			$InsertionOrderLineItemFactory = \_factory\InsertionOrderLineItem::get_instance();
			$params = array();
			$params["InsertionOrderID"] = $id;
			$params["Active"] = 1;
			$rtb_banner_list = $InsertionOrderLineItemFactory->get($params);

		endif;

		$navigation = $this->getServiceLocator()->get('navigation');
                $page = $navigation->findBy('id', 'ViewBannerLevel');
                $page->set("label","View Banners (" . $this->getBreadCrumbInfoFromInsertionOrder($id, $campaign_preview_id, $is_preview)["BCInsertionOrder"] . ")");
                $page->set("params", array("param1" => $id));

        if ($is_preview == true):
        	$ad_campaign_id = $campaign_preview_id;
   		else:
        	$ad_campaign_id = $id;
     	endif;
                
		return new ViewModel(array(
				'ispreview'	  => $is_preview == true ? '1' : '0',
				'rtb_banners' => $rtb_banner_list,
		        'campaign_id' => $id,
				'campaign_preview_id' => $campaign_preview_id,
				'bread_crumb_info' => $this->getBreadCrumbInfoFromInsertionOrder($id, $campaign_preview_id, $is_preview),
				'user_id_list' => $this->user_id_list_demand_customer,
	    		'user_identity' => $this->identity(),
				'true_user_name' => $this->auth->getUserName(),
				'header_title' => '<a href="/demand/createlineitem/' . $ad_campaign_id . $this->preview_query . '">Create New Line Item</a>',
				'is_super_admin' => $this->is_super_admin,
				'effective_id' => $this->auth->getEffectiveIdentityID(),
				'impersonate_id' => $this->ImpersonateID
		));
	}

	/**
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
	public function createlineitemAction() {
	    $id = $this->getEvent()->getRouteMatch()->getParam('param1');
        if ($id == null):
            die("Invalid Campaign ID");
        endif;

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

        $is_preview = $this->getRequest()->getQuery('ispreview');

        // verify
        if ($is_preview == "true"):
        	$is_preview = \transformation\TransformPreview::doesPreviewInsertionOrderExist($id, $this->auth);
        endif;

        $campaignpreviewid = "";

        if ($is_preview == "true"):
	        // ACL PERMISSIONS CHECK
	        transformation\CheckPermissions::checkEditPermissionInsertionOrderPreview($id, $this->auth, $this->config_handle);
        	$campaignpreviewid = $id;
        	$id = "";
        else:
	        // ACL PERMISSIONS CHECK
	        transformation\CheckPermissions::checkEditPermissionInsertionOrder($id, $this->auth, $this->config_handle);
        endif;

        $current_mimes 					= array();
        $current_apis_supported 		= array();
        $current_protocols 				= array();
        $current_delivery_methods 		= array();
        $current_playback_methods 		= array();
        
        $current_start_delay 			= "";
        $current_linearity 				= "";
        
        return new ViewModel(array(
        		'ispreview'	  				=> $is_preview == true ? '1' : '0',
        		'campaignid'       			=> $id,
        		'campaignpreviewid' 		=> $campaignpreviewid,
        		'adcampaigntype_options'   	=> $this->getInsertionOrderTypeOptions(),
                'mobile_options'    		=> \util\BannerOptions::$mobile_options,
                'size_list'         		=> \util\BannerOptions::$iab_banner_options,
				'bread_crumb_info' 			=> $this->getBreadCrumbInfoFromInsertionOrder($id, $campaignpreviewid, $is_preview),
        		'user_id_list' => $this->user_id_list_demand_customer,
    			'center_class' 				=> 'centerj',
	    		'user_identity' 			=> $this->identity(),
	    		'true_user_name' => $this->auth->getUserName(),
				'header_title' => 'Create Line Item',
				'is_super_admin' => $this->is_super_admin,
				'effective_id' => $this->auth->getEffectiveIdentityID(),
				'impersonate_id' => $this->ImpersonateID,
        		
        		'linearity' => \util\BannerOptions::$linearity,
        		'start_delay' => \util\BannerOptions::$start_delay,
        		'playback_methods' => \util\BannerOptions::$playback_methods,
        		'delivery_methods' => \util\BannerOptions::$delivery_methods,
        		'apis_supported' => \util\BannerOptions::$apis_supported,
        		'protocols' => \util\BannerOptions::$protocols,
        		'mimes' => \util\BannerOptions::$mimes,
        		
        		'current_mimes' => $current_mimes,
        		'current_apis_supported' => $current_apis_supported,
        		'current_protocols' => $current_protocols,
        		'current_delivery_methods' => $current_delivery_methods,
        		'current_playback_methods' => $current_playback_methods,
        		'current_start_delay' => $current_start_delay,
        		'current_linearity' => $current_linearity,
        ));
	}

	private function getInsertionOrderTypeOptions() {
		
		$InsertionOrderTypeFactory = \_factory\InsertionOrderType::get_instance();
		$params = array();
		$InsertionOrderTypeList = $InsertionOrderTypeFactory->get($params);
		
		$adcampaigntype_options = array();
		
		foreach ($InsertionOrderTypeList as $InsertionOrderType):

			$adcampaigntype_options[$InsertionOrderType->InsertionOrderTypeID] = $InsertionOrderType->Description;
		
		endforeach;
		
		return $adcampaigntype_options;
	}
	
	/**
	 * 
	 * @return Ambigous <\Zend\View\Model\ViewModel, \Zend\View\Model\ViewModel>
	 */
	public function newbannerAction() {

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		$ImpressionType = $this->getRequest()->getPost('ImpressionType');
		
		if ($ImpressionType != 'banner' && $ImpressionType != 'video'):
			die("Required Field: ImpressionType was missing");
		endif;
		
		$needed_input_banner = array(
				'bannername',
				'startdate',
				'enddate',
				'ismobile',
				'iabsize',
				'height',
				'width',
				'bidamount',
				'adtag',
				'landingpagetld'
		);
		
		$needed_input_video = array(
				'bannername',
				'startdate',
				'enddate',
				'bidamount',
				'adtag',
				'landingpagetld'
		);
		
		if ($ImpressionType == 'video'):
			$this->validateInput($needed_input_video);
		else:
			$this->validateInput($needed_input_banner);
		endif;
		
		$adcampaigntype 		= AD_TYPE_ANY_REMNANT;
		$linkedzones 			= array();
		
		$campaignid 			= $this->getRequest()->getPost('campaignid');
		$campaign_preview_id 	= $this->getRequest()->getPost('campaignpreviewid');
		$bannerid 				= $this->getRequest()->getPost('bannerid');
		$banner_preview_id 		= $this->getRequest()->getPost('bannerpreviewid');
		$ispreview 				= $this->getRequest()->getPost('ispreview');

		if ($ispreview != true):
			/*
			 * THIS METHOD CHECKS IF THERE IS AN EXISTING PREVIEW MODE CAMPAIGN
			* IF NOT, IT CHECKS THE ACL PERMISSIONS ON THE PRODUCTION BANNER/CAMPAIGN REFERENCED
			* THEN IT CREATES A PREVIEW VERSION OF THE AD CAMPAIGN
			*/

			if ($bannerid != null):
				$update_data = array('type'=>'InsertionOrderLineItemID', 'id'=>$bannerid);
			else:
				$update_data = array('type'=>'InsertionOrderID', 'id'=>$campaignid);
			endif;

			$return_val = \transformation\TransformPreview::previewCheckInsertionOrderID($campaignid, $this->auth, $this->config_handle, $this->getServiceLocator()->get('mail.transport'), $update_data);

			if ($return_val !== null):
				if ($bannerid != null):
					$campaign_preview_id 	= $return_val["InsertionOrderPreviewID"];
					$banner_preview_id 		= $return_val["InsertionOrderLineItemPreviewID"];
				else:
					$campaign_preview_id 	= $return_val["InsertionOrderPreviewID"];
				endif;
			endif;
		endif;

		// ACL PREVIEW PERMISSIONS CHECK
		transformation\CheckPermissions::checkEditPermissionInsertionOrderPreview($campaign_preview_id, $this->auth, $this->config_handle);

		$bannername = $this->getRequest()->getPost('bannername');
		$startdate = $this->getRequest()->getPost('startdate');
		$enddate = $this->getRequest()->getPost('enddate');
		if ($this->is_super_admin):
			$adcampaigntype 		= $this->getRequest()->getPost('adcampaigntype');
			$linkedzones 			= $this->getRequest()->getPost('linkedzones');
		endif;
		$ismobile = $this->getRequest()->getPost('ismobile');
		$iabsize = $this->getRequest()->getPost('iabsize');
		$height = $this->getRequest()->getPost('height');
		$width = $this->getRequest()->getPost('width');
		$weight = $this->getRequest()->getPost('weight');
		$bidamount = $this->getRequest()->getPost('bidamount');
		$adtag = $this->getRequest()->getPost('adtag');
		$landingpagetld = $this->getRequest()->getPost('landingpagetld');
		$bannerid = $this->getRequest()->getPost('bannerid');

		if ($ImpressionType == 'video'):

			$mimes 						= $this->getRequest()->getPost("Mimes");
			if ($mimes && is_array($mimes) && count($mimes) > 0):
				$mimes = join(',', $mimes);
			else:
				$mimes = "";
			endif;
					
			$protocols 					= $this->getRequest()->getPost("Protocols");
			if ($protocols && is_array($protocols) && count($protocols) > 0):
				$protocols = join(',', $protocols);
			else:
				$protocols = "";
			endif;
					
			$apis_supported 			= $this->getRequest()->getPost("ApisSupported");
			if ($apis_supported && is_array($apis_supported) && count($apis_supported) > 0):
				$apis_supported = join(',', $apis_supported);
			else:
				$apis_supported = "";
			endif;
					
			$delivery 					= $this->getRequest()->getPost("Delivery");
			if ($delivery && is_array($delivery) && count($delivery) > 0):
				$delivery = join(',', $delivery);
			else:
				$delivery = "";
			endif;
					
			$playback 					= $this->getRequest()->getPost("Playback");
			if ($playback && is_array($playback) && count($playback) > 0):
				$playback = join(',', $playback);
			else:
				$playback = "";
			endif;
					
			$start_delay 				= $this->getRequest()->getPost("StartDelay");
					
			$linearity 					= $this->getRequest()->getPost("Linearity");

		endif;
		
		$BannerPreview = new \model\InsertionOrderLineItemPreview();
		if ($banner_preview_id != null):
		  $BannerPreview->InsertionOrderLineItemPreviewID             = $banner_preview_id;
		endif;

		$BannerPreview->UserID             	= $this->auth->getEffectiveUserID();

		$BannerPreview->Name                      = $bannername;
		$BannerPreview->InsertionOrderPreviewID       = $campaign_preview_id;
		$BannerPreview->StartDate                 = date("Y-m-d H:i:s", strtotime($startdate));
		$BannerPreview->EndDate                   = date("Y-m-d H:i:s", strtotime($enddate));
		if ($this->is_super_admin || $banner_preview_id == null):
			$BannerPreview->InsertionOrderTypeID      = $adcampaigntype;
		endif;
		
		$BannerPreview->ImpressionType			  = $ImpressionType;
		$BannerPreview->IsMobile                  = $ismobile;
		$BannerPreview->IABSize                   = $iabsize;
		$BannerPreview->Height                    = $height;
		$BannerPreview->Width                     = $width;
		$BannerPreview->Weight          		  = $weight == null ? 5 : $weight;
		$BannerPreview->BidAmount                 = $bidamount;
		$BannerPreview->AdTag                     = trim($adtag);
		$BannerPreview->DeliveryType              = 'js';
		$BannerPreview->LandingPageTLD            = $landingpagetld;
		$BannerPreview->ImpressionsCounter        = 0;
		$BannerPreview->BidsCounter               = 0;
		$BannerPreview->CurrentSpend              = 0;
		$BannerPreview->Active                    = 1;
		$BannerPreview->DateCreated               = date("Y-m-d H:i:s");
		$BannerPreview->DateUpdated               = date("Y-m-d H:i:s");
		$BannerPreview->ChangeWentLive       	  = 0;

		$InsertionOrderLineItemPreviewFactory = \_factory\InsertionOrderLineItemPreview::get_instance();
		$banner_preview_id_new = $InsertionOrderLineItemPreviewFactory->saveInsertionOrderLineItemPreview($BannerPreview);

		if ($banner_preview_id_new != null):
			$banner_preview_id = $banner_preview_id_new;
		elseif ($BannerPreview->InsertionOrderLineItemPreviewID == null):
			$BannerPreview->InsertionOrderLineItemPreviewID = $banner_preview_id;
		endif;
		
		$InsertionOrderLineItemVideoRestrictionsPreviewFactory = \_factory\InsertionOrderLineItemVideoRestrictionsPreview::get_instance();
		$InsertionOrderLineItemRestrictionsPreviewFactory = \_factory\InsertionOrderLineItemRestrictionsPreview::get_instance();
		
		if ($ImpressionType == 'video'):

			$params = array();
			$params["InsertionOrderLineItemPreviewID"] = $banner_preview_id;
			$InsertionOrderLineItemVideoRestrictionsPreview = $InsertionOrderLineItemVideoRestrictionsPreviewFactory->get_row($params);
			
			if ($InsertionOrderLineItemVideoRestrictionsPreview == null):
			
				$InsertionOrderLineItemVideoRestrictionsPreview = new \model\InsertionOrderLineItemVideoRestrictionsPreview();
				
			endif;
			
			$InsertionOrderLineItemVideoRestrictionsPreview->InsertionOrderLineItemPreviewID 			= $banner_preview_id;

			$InsertionOrderLineItemVideoRestrictionsPreview->DateCreated               			= date("Y-m-d H:i:s");
			
			$InsertionOrderLineItemVideoRestrictionsPreview->MimesCommaSeparated 				= trim($mimes);
			$InsertionOrderLineItemVideoRestrictionsPreview->ProtocolsCommaSeparated 			= trim($protocols);
			$InsertionOrderLineItemVideoRestrictionsPreview->ApisSupportedCommaSeparated 		= trim($apis_supported);
			$InsertionOrderLineItemVideoRestrictionsPreview->DeliveryCommaSeparated 			= trim($delivery);
			$InsertionOrderLineItemVideoRestrictionsPreview->PlaybackCommaSeparated 			= trim($playback);
			
			$InsertionOrderLineItemVideoRestrictionsPreview->StartDelay 						= trim($start_delay);
			$InsertionOrderLineItemVideoRestrictionsPreview->Linearity 							= trim($linearity);

			
			$InsertionOrderLineItemVideoRestrictionsPreviewFactory->saveInsertionOrderLineItemVideoRestrictionsPreview($InsertionOrderLineItemVideoRestrictionsPreview);
			
			$InsertionOrderLineItemRestrictionsPreviewFactory->deleteInsertionOrderLineItemRestrictionsPreview($banner_preview_id);
			
		else:
		
			$InsertionOrderLineItemVideoRestrictionsPreviewFactory->deleteInsertionOrderLineItemVideoRestrictionsPreview($banner_preview_id);
			
		endif;
		
		if ($this->is_super_admin):
		
			$LinkedBannerToAdZonePreviewFactory = \_factory\LinkedBannerToAdZonePreview::get_instance();
			$LinkedBannerToAdZonePreviewFactory->deleteLinkedBannerToAdZonePreview($BannerPreview->InsertionOrderLineItemPreviewID);
			
			// campaigntype AD_TYPE_CONTRACT case
			if ($adcampaigntype == AD_TYPE_CONTRACT && $linkedzones != null && count($linkedzones) > 0):
			
				foreach($linkedzones as $linked_zone_id):
					
					$LinkedBannerToAdZonePreview = new \model\LinkedBannerToAdZonePreview();
					$LinkedBannerToAdZonePreview->InsertionOrderLineItemPreviewID = $BannerPreview->InsertionOrderLineItemPreviewID;
					$LinkedBannerToAdZonePreview->PublisherAdZoneID			= intval($linked_zone_id);
					$LinkedBannerToAdZonePreview->Weight					= intval($weight);
					$LinkedBannerToAdZonePreview->DateCreated				= date("Y-m-d H:i:s");
					$LinkedBannerToAdZonePreview->DateUpdated				= date("Y-m-d H:i:s");
					$LinkedBannerToAdZonePreviewFactory->saveLinkedBannerToAdZonePreview($LinkedBannerToAdZonePreview);
				endforeach;
			
			endif;
			
		endif;

		$refresh_url = "/demand/viewlineitem/" . $BannerPreview->InsertionOrderPreviewID . "?ispreview=true";
		$viewModel = new ViewModel(array('refresh_url' => $refresh_url));

		return $viewModel->setTemplate('dashboard-manager/demand/interstitial.phtml');

	}

	/**
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
	public function editlineitemAction() {

		$id = $this->getEvent()->getRouteMatch()->getParam('param1');
		if ($id == null):
		  die("Invalid Banner ID");
		endif;

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		$is_preview = $this->getRequest()->getQuery('ispreview');
		
		// verify
		if ($is_preview == "true"):
			$is_preview = \transformation\TransformPreview::doesPreviewBannerExist($id, $this->auth);
		endif;
		$banner_preview_id = "";

		if ($is_preview == true):

			// ACL PREVIEW PERMISSIONS CHECK
			transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($id, $this->auth, $this->config_handle);

			$InsertionOrderLineItemVideoRestrictionsPreviewFactory = \_factory\InsertionOrderLineItemVideoRestrictionsPreview::get_instance();
			$params = array();
			$params["InsertionOrderLineItemPreviewID"] = $id;
			$InsertionOrderLineItemVideoRestrictions = $InsertionOrderLineItemVideoRestrictionsPreviewFactory->get_row($params);
			
			$InsertionOrderLineItemPreviewFactory = \_factory\InsertionOrderLineItemPreview::get_instance();
			$params = array();
			$params["Active"] = 1;
			$params["InsertionOrderLineItemPreviewID"] = $id;
			$banner_preview_id = $id;

			$InsertionOrderLineItem = $InsertionOrderLineItemPreviewFactory->get_row($params);

		else:
			// ACL PERMISSIONS CHECK
			transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItem($id, $this->auth, $this->config_handle);

			$InsertionOrderLineItemVideoRestrictionsFactory = \_factory\InsertionOrderLineItemVideoRestrictions::get_instance();
			$params = array();
			$params["InsertionOrderLineItemID"] = $id;
			
			$InsertionOrderLineItemVideoRestrictions = $InsertionOrderLineItemVideoRestrictionsFactory->get_row($params);

			$InsertionOrderLineItemFactory = \_factory\InsertionOrderLineItem::get_instance();
			$params = array();
			$params["Active"] = 1;
			$params["InsertionOrderLineItemID"] = $id;

			$InsertionOrderLineItem = $InsertionOrderLineItemFactory->get_row($params);

		endif;

		if ($InsertionOrderLineItem == null):
		  die("Invalid $InsertionOrderLineItem ID");
		endif;

		$campaignid               = isset($InsertionOrderLineItem->InsertionOrderID) ? $InsertionOrderLineItem->InsertionOrderID : "";
		$bannerid                 = isset($InsertionOrderLineItem->InsertionOrderLineItemID) ? $InsertionOrderLineItem->InsertionOrderLineItemID : "";
		$campaignpreviewid        = isset($InsertionOrderLineItem->InsertionOrderPreviewID) ? $InsertionOrderLineItem->InsertionOrderPreviewID : "";
		$bannerpreviewid          = isset($InsertionOrderLineItem->InsertionOrderLineItemPreviewID) ? $InsertionOrderLineItem->InsertionOrderLineItemPreviewID : "";
		$bannername               = $InsertionOrderLineItem->Name;
		$startdate                = date('m/d/Y', strtotime($InsertionOrderLineItem->StartDate));
		$enddate                  = date('m/d/Y', strtotime($InsertionOrderLineItem->EndDate));
		$current_adcampaigntype   = $InsertionOrderLineItem->InsertionOrderTypeID;
		$current_mobile           = $InsertionOrderLineItem->IsMobile;
		if ($InsertionOrderLineItem->IsMobile == 2):
		      $size_list                = \util\BannerOptions::$iab_mobile_tablet_banner_options;
		elseif ($InsertionOrderLineItem->IsMobile > 0):
		      $size_list                = \util\BannerOptions::$iab_mobile_phone_banner_options;
		else:
		      $size_list                = \util\BannerOptions::$iab_banner_options;
		endif;
		$height                   = $InsertionOrderLineItem->Height;
		$width                    = $InsertionOrderLineItem->Width;
		$weight                   = $InsertionOrderLineItem->Weight;
		$bidamount                = $InsertionOrderLineItem->BidAmount;
		$adtag                    = $InsertionOrderLineItem->AdTag;
		$landingpagetld           = $InsertionOrderLineItem->LandingPageTLD;
		$current_iabsize          = $InsertionOrderLineItem->IABSize;
		
		$ImpressionType           = $InsertionOrderLineItem->ImpressionType;

		$current_mimes 					= array();
		$current_apis_supported 		= array();
		$current_protocols 				= array();
		$current_delivery_methods 		= array();
		$current_playback_methods 		= array();
		
		$current_start_delay 			= "";
		$current_linearity 				= "";
		
		$impression_type				= "banner";
		
		if ($InsertionOrderLineItemVideoRestrictions != null):
		
			$current_mimes_raw = $InsertionOrderLineItemVideoRestrictions->MimesCommaSeparated;
			$current_apis_supported_raw = $InsertionOrderLineItemVideoRestrictions->ApisSupportedCommaSeparated;
			$current_protocols_raw = $InsertionOrderLineItemVideoRestrictions->ProtocolsCommaSeparated;
			$current_delivery_methods_raw = $InsertionOrderLineItemVideoRestrictions->DeliveryCommaSeparated;
			$current_playback_methods_raw = $InsertionOrderLineItemVideoRestrictions->PlaybackCommaSeparated;
			
			$current_start_delay = $InsertionOrderLineItemVideoRestrictions->StartDelay;
			$current_linearity = $InsertionOrderLineItemVideoRestrictions->Linearity;

			$current_mimes = array();
			
			if ($current_mimes_raw):
			
				$current_mimes = explode(',', $current_mimes_raw);
			
			endif;
			
			$current_apis_supported = array();
			
			if ($current_apis_supported_raw):
			
				$current_apis_supported = explode(',', $current_apis_supported_raw);
			
			endif;
			
			$current_protocols = array();
			
			if ($current_protocols_raw):
			
				$current_protocols = explode(',', $current_protocols_raw);
			
			endif;
			
			$current_delivery_methods = array();
			
			if ($current_delivery_methods_raw):
			
				$current_delivery_methods = explode(',', $current_delivery_methods_raw);
			
			endif;
			
			$current_playback_methods = array();
			
			if ($current_playback_methods_raw):
			
				$current_playback_methods = explode(',', $current_playback_methods_raw);
			
			endif;
			
		endif;

		$is_vast_url = \util\ParseHelper::isVastURL($adtag);
		$vast_type = $is_vast_url == true ? "url" : "xml";
		
		return new ViewModel(array(
				'campaignid'              => $campaignid,
		        'bannerid'                => $bannerid,
				'campaignpreviewid'       => $campaignpreviewid,
				'bannerpreviewid'         => $bannerpreviewid,
				'ispreview' 			  => $is_preview == true ? '1' : '0',
    		    'bannername'              => $bannername,
    		    'startdate'               => $startdate,
    		    'enddate'                 => $enddate,
				'current_adcampaigntype'  => $current_adcampaigntype,
				'adcampaigntype_options'  => $this->getInsertionOrderTypeOptions(),
				'current_mobile'          => $current_mobile,
		        'mobile_options'          => \util\BannerOptions::$mobile_options,
    		    'size_list'               => $size_list,
    		    'height'                  => $height,
    		    'width'                   => $width,
				'weight'                  => $weight,
    		    'bidamount'               => $bidamount,
    		    'adtag'                   => $adtag,
				'vast_type'			      => $vast_type,
		        'landingpagetld'          => $landingpagetld,
    		    'current_iabsize'         => $current_iabsize,
				'bread_crumb_info'		  => $this->getBreadCrumbInfoFromBanner($bannerid, $bannerpreviewid, $is_preview),
				'user_id_list' => $this->user_id_list_demand_customer,
    			'center_class' => 'centerj',
	    		'user_identity' => $this->identity(),
				'true_user_name' => $this->auth->getUserName(),
				'header_title' => 'Edit Insertion Order',
				'is_super_admin' => $this->is_super_admin,
				'effective_id' => $this->auth->getEffectiveIdentityID(),
				'impersonate_id' => $this->ImpersonateID,
				'ImpressionType' => $ImpressionType,
				
				'linearity' => \util\BannerOptions::$linearity,
				'start_delay' => \util\BannerOptions::$start_delay,
				'playback_methods' => \util\BannerOptions::$playback_methods,
				'delivery_methods' => \util\BannerOptions::$delivery_methods,
				'apis_supported' => \util\BannerOptions::$apis_supported,
				'protocols' => \util\BannerOptions::$protocols,
				'mimes' => \util\BannerOptions::$mimes,
				
				'current_mimes' => $current_mimes,
				'current_apis_supported' => $current_apis_supported,
				'current_protocols' => $current_protocols,
				'current_delivery_methods' => $current_delivery_methods,
				'current_playback_methods' => $current_playback_methods,
				'current_start_delay' => $current_start_delay,
				'current_linearity' => $current_linearity,
				
				'impression_type' => $impression_type
		));
	}

	/**
	 *
	 * @return JSON encoded data for AJAX call
	 */
	public function editlinkedzoneAction() {

		$id 		= $this->getEvent()->getRouteMatch()->getParam('param1');
		$height 	= $this->getRequest()->getQuery('height');
		$width 		= $this->getRequest()->getQuery('width');
		$is_preview = $this->getRequest()->getQuery('is_preview');
				
		if ($height == null || $width == null):
			$data = array(
					'success' => false,
					'linked_ad_zones' => "", 
					'complete_zone_list' => array()
			);
			return $this->getResponse()->setContent(json_encode($data));
		endif;
	
		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;
		
		if (!$this->is_super_admin):
			$data = array(
					'success' => false,
					'linked_ad_zones' => "", 
					'complete_zone_list' => array()
			);
			return $this->getResponse()->setContent(json_encode($data));
		endif;

		// verify
		if ($is_preview == "true"):
			$is_preview = \transformation\TransformPreview::doesPreviewBannerExist($id, $this->auth);
		endif;
		$banner_preview_id = "";
		$linked_ad_zones = array();

		if ($id != null):

			if ($is_preview === true):

				// ACL PREVIEW PERMISSIONS CHECK
				transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItemPreview($id, $this->auth, $this->config_handle);
			
				$InsertionOrderLineItemPreviewFactory = \_factory\InsertionOrderLineItemPreview::get_instance();
				$params = array();
				$params["Active"] = 1;
				$params["InsertionOrderLineItemPreviewID"] = $id;
				$banner_preview_id = $id;
			
				$InsertionOrderLineItem = $InsertionOrderLineItemPreviewFactory->get_row($params);
				
				$LinkedBannerToAdZonePreviewFactory = \_factory\LinkedBannerToAdZonePreview::get_instance();
				$params = array();
				$params["InsertionOrderLineItemPreviewID"] = $id;

				$linked_ad_zones = $LinkedBannerToAdZonePreviewFactory->get($params);
				
			else:
			
				// ACL PERMISSIONS CHECK
				transformation\CheckPermissions::checkEditPermissionInsertionOrderLineItem($id, $this->auth, $this->config_handle);
			
				$InsertionOrderLineItemFactory = \_factory\InsertionOrderLineItem::get_instance();
				$params = array();
				$params["Active"] = 1;
				$params["InsertionOrderLineItemID"] = $id;
			
				$InsertionOrderLineItem = $InsertionOrderLineItemFactory->get_row($params);
	
				$LinkedBannerToAdZoneFactory = \_factory\LinkedBannerToAdZone::get_instance();
				$params = array();
				$params["InsertionOrderLineItemID"] = $id;
				$linked_ad_zones = $LinkedBannerToAdZoneFactory->get($params);
				
			endif;
		endif;
		
		$params = array();
		$params["Height"] = $height;
		$params["Width"] = $width;
		// $params["AdOwnerID"] = \transformation\UserToPublisher::user_id_to_publisher_info_id($this->auth->getEffectiveUserID());
		
		$PublisherAdZoneFactory = \_factory\PublisherAdZone::get_instance();
		$PublisherAdZoneList = $PublisherAdZoneFactory->get($params);
		if ($PublisherAdZoneList === null):
			$PublisherAdZoneList = array();
		endif;
		
		$complete_zone_list = array();
		
		foreach ($PublisherAdZoneList as $PublisherAdZone):
		
			$complete_zone_list[] = array(
									"zone_id"	=>$PublisherAdZone->PublisherAdZoneID,
									"ad_name"	=>$PublisherAdZone->AdName
			);
		
		endforeach;
		
		$linked_zone_list = array();
		
		foreach ($linked_ad_zones as $linked_ad_zone):
		
			$linked_zone_list[] = $linked_ad_zone->PublisherAdZoneID;
			
		endforeach;
		
		$data = array(
				'success' => count($PublisherAdZoneList) > 0,
				'linked_ad_zones' => implode(',', $linked_zone_list), 
				'complete_zone_list' => $complete_zone_list
		);
		
		return $this->getResponse()->setContent(json_encode($data));

	}
	
	/*
	 * END NGINAD InsertionOrderLineItem Actions
	*/

	/*
	 * BEGIN NGINAD InsertionOrder Actions
	*/

	/**
	 * 
	 * @return Ambigous <\Zend\View\Model\ViewModel, \Zend\View\Model\ViewModel>
	 */
	public function deleteinsertionorderAction() {

		$error_msg = null;
		$success = true;
		$id = $this->getEvent()->getRouteMatch()->getParam('param1');
		if ($id == null):
		  //die("Invalid Campaign ID");
		  $error_msg = "Invalid Campaign ID";
		  $success = false;
		  $data = array(
	        'success' => $success,
	        'data' => array('error_msg' => $error_msg)
   		 );
   		 
         return $this->getResponse()->setContent(json_encode($data));
		endif;

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		$is_preview = $this->getRequest()->getQuery('ispreview');

		// verify
		if ($is_preview != "true"):
			/*
			 * THIS METHOD CHECKS IF THERE IS AN EXISTING PREVIEW MODE CAMPAIGN
			* IF NOT, IT CHECKS THE ACL PERMISSIONS ON THE PRODUCTION BANNER/CAMPAIGN REFERENCED
			* THEN IT CREATES A PREVIEW VERSION OF THE AD CAMPAIGN
			*/

			$update_data = array('type'=>'InsertionOrderID', 'id'=>$id);
			$return_val = \transformation\TransformPreview::previewCheckInsertionOrderID($id, $this->auth, $this->config_handle, $this->getServiceLocator()->get('mail.transport'), $update_data);
			
			if ($return_val !== null && array_key_exists("error", $return_val)):

				$success = false;
				$data = array(
			       'success' => $success,
			       'data' => array('error_msg' => $return_val['error'])
		   		);
   		
		   	   return $this->getResponse()->setContent(json_encode($data));
			endif;

			if ($return_val !== null):
				$id = $return_val["InsertionOrderPreviewID"];
			endif;
		endif;

		// ACL PREVIEW PERMISSIONS CHECK
		//transformation\CheckPermissions::checkEditPermissionInsertionOrderPreview($id, $auth, $config);
		$response = transformation\CheckPermissions::checkEditPermissionInsertionOrderPreview($id, $this->auth, $this->config_handle);

		if(array_key_exists("error", $response) > 0):
			$success = false;
			$data = array(
		       'success' => $success,
		       'data' => array('error_msg' => $response['error'])
	   		);
	   		
	   	   return $this->getResponse()->setContent(json_encode($data));
		endif;

		$InsertionOrderPreviewFactory = \_factory\InsertionOrderPreview::get_instance();
		$params = array();
		$params["InsertionOrderPreviewID"] = $id;

		$InsertionOrderPreview = $InsertionOrderPreviewFactory->get_row($params);

		if ($InsertionOrderPreview == null):
		  //die("Invalid InsertionOrder Preview ID");
		  $error_msg = "Invalid InsertionOrder Preview ID";
		  $success = false;
		  $data = array(
	        'success' => $success,
	        'data' => array('error_msg' => $error_msg)
   		 );
   		 
         return $this->getResponse()->setContent(json_encode($data));
		endif;

		$ad_campaign_preview_id = $InsertionOrderPreview->InsertionOrderPreviewID;

		$InsertionOrderLineItemPreviewFactory = \_factory\InsertionOrderLineItemPreview::get_instance();
		$params = array();
		$params["InsertionOrderPreviewID"] = $InsertionOrderPreview->InsertionOrderPreviewID;

		$InsertionOrderLineItemPreviewList = $InsertionOrderLineItemPreviewFactory->get($params);

        foreach ($InsertionOrderLineItemPreviewList as $InsertionOrderLineItemPreview):

            $banner_preview_id = $InsertionOrderLineItemPreview->InsertionOrderLineItemPreviewID;
    		$InsertionOrderLineItemPreviewFactory->deActivateInsertionOrderLineItemPreview($banner_preview_id);

		endforeach;

    	$InsertionOrderPreviewFactory->doDeletedInsertionOrderPreview($ad_campaign_preview_id);

		$data = array(
	        'success' => $success,
	        'data' => array('error_msg' => $error_msg)
   		 );
   		 
         return $this->getResponse()->setContent(json_encode($data));
		
		/*$refresh_url = "/demand/?ispreview=true";
		$viewModel = new ViewModel(array('refresh_url' => $refresh_url));

		return $viewModel->setTemplate('dashboard-manager/demand/interstitial.phtml');*/

	}

	/**
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
	public function editinsertionorderAction() {
		$id = $this->getEvent()->getRouteMatch()->getParam('param1');
		if ($id == null):
			die("Invalid Campaign ID");
		endif;

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

		$is_preview = $this->getRequest()->getQuery('ispreview');

		// verify
		if ($is_preview == "true"):
			$is_preview = \transformation\TransformPreview::doesPreviewInsertionOrderExist($id, $this->auth);
		endif;
		$campaign_preview_id = "";

		if ($is_preview == true):

			// ACL PREVIEW PERMISSIONS CHECK
			transformation\CheckPermissions::checkEditPermissionInsertionOrderPreview($id, $this->auth, $this->config_handle);

			$InsertionOrderPreviewFactory = \_factory\InsertionOrderPreview::get_instance();
			$params = array();
			$params["InsertionOrderPreviewID"] = $id;
			$params["Active"] = 1;

			$InsertionOrder = $InsertionOrderPreviewFactory->get_row($params);

			$campaign_preview_id = $id;
			$id = "";

		else:
			// ACL PERMISSIONS CHECK
			transformation\CheckPermissions::checkEditPermissionInsertionOrder($id, $this->auth, $this->config_handle);

			$InsertionOrderFactory = \_factory\InsertionOrder::get_instance();
			$params = array();
			$params["InsertionOrderID"] = $id;
			$params["Active"] = 1;

			$InsertionOrder = $InsertionOrderFactory->get_row($params);

		endif;

		if ($InsertionOrder == null):
			die("Invalid InsertionOrder ID");
		endif;

		$campaignname              = $InsertionOrder->Name;
		$startdate                 = date('m/d/Y', strtotime($InsertionOrder->StartDate));
		$enddate                   = date('m/d/Y', strtotime($InsertionOrder->EndDate));
		$customername              = $InsertionOrder->Customer;
		$customerid                = $InsertionOrder->CustomerID;
		$maximpressions            = $InsertionOrder->MaxImpressions;
		$maxspend                  = sprintf("%1.2f", $InsertionOrder->MaxSpend);


		return new ViewModel(array(
				'campaignid' => $id,
				'campaignpreviewid' => $campaign_preview_id,
				'ispreview' => $is_preview == true ? '1' : '0',
				'campaignname' => $campaignname,
				'startdate' => $startdate,
				'enddate' => $enddate,
				'customername' => $customername,
				'customerid' => $customerid,
				'maximpressions' => $maximpressions,
				'maxspend' => $maxspend,
				'bread_crumb_info' => $this->getBreadCrumbInfoFromInsertionOrder($id, $campaign_preview_id, $is_preview),
				'user_id_list' => $this->user_id_list_demand_customer,
    			'center_class' => 'centerj',
	    		'user_identity' => $this->identity(),
	    		'true_user_name' => $this->auth->getUserName(),
				'header_title' => 'Edit Insertion Order',
				'is_super_admin' => $this->is_super_admin,
				'effective_id' => $this->auth->getEffectiveIdentityID(),
				'impersonate_id' => $this->ImpersonateID
		));
	}

	/**
	 * This function does ZERO, right now. Empty.
	 */
	public function createinsertionorderAction() {

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;
		
		return new ViewModel(array(
				'ispreview'	  => "true",
				'user_id_list' => $this->user_id_list_demand_customer,
				'user_identity' => $this->identity(),
	    		'true_user_name' => $this->auth->getUserName(),
				'header_title' => 'Create New Insertion Order',
				'is_super_admin' => $this->is_super_admin,
				'effective_id' => $this->auth->getEffectiveIdentityID(),
				'impersonate_id' => $this->ImpersonateID
		));
	    		
	}

	/**
	 * 
	 * @return Ambigous <\Zend\View\Model\ViewModel, \Zend\View\Model\ViewModel>
	 */
	public function newcampaignAction() {

	    $needed_input = array(
	        'campaignname',
	        'startdate',
	        'enddate',
	        'maximpressions',
	        'maxspend'
	    );

		$initialized = $this->initialize();
		if ($initialized !== true) return $initialized;

	    $this->validateInput($needed_input);

	    $campaignname = $this->getRequest()->getPost('campaignname');
	    $startdate = $this->getRequest()->getPost('startdate');
	    $enddate = $this->getRequest()->getPost('enddate');
	    $customername = $this->getRequest()->getPost('customername');
	    $customerid = $this->getRequest()->getPost('customerid');
	    if (!$customerid) $customerid = "001";
	    $maximpressions = $this->getRequest()->getPost('maximpressions');
	    $maxspend = $this->getRequest()->getPost('maxspend');
	    $campaignid = $this->getRequest()->getPost('campaignid');
	    $campaign_preview_id 		= $this->getRequest()->getPost('campaignpreviewid');
	    $ispreview 					= $this->getRequest()->getPost('ispreview');

	    $InsertionOrderPreview = new \model\InsertionOrderPreview();

	    if ($campaignid != null && $ispreview != true):
		    /*
		     * THIS METHOD CHECKS IF THERE IS AN EXISTING PREVIEW MODE CAMPAIGN
		    * IF NOT, IT CHECKS THE ACL PERMISSIONS ON THE PRODUCTION BANNER/CAMPAIGN REFERENCED
		    * THEN IT CREATES A PREVIEW VERSION OF THE AD CAMPAIGN
		    */

		    $update_data = array('type'=>'InsertionOrderID', 'id'=>$campaignid);
		    $return_val = \transformation\TransformPreview::previewCheckInsertionOrderID($campaignid, $this->auth, $this->config_handle, $this->getServiceLocator()->get('mail.transport'), $update_data);

		    if ($return_val !== null):
			    $campaign_preview_id 	= $return_val["InsertionOrderPreviewID"];
		    endif;

		    $InsertionOrderPreview->InsertionOrderID 	= $campaignid;

	    endif;


	    if ($campaign_preview_id != null):

		    // ACL PREVIEW PERMISSIONS CHECK
		    transformation\CheckPermissions::checkEditPermissionInsertionOrderPreview($campaign_preview_id, $this->auth, $this->config_handle);
	       	$InsertionOrderPreview->InsertionOrderPreviewID               = $campaign_preview_id;

	       	$params = array();
	       	$params["InsertionOrderPreviewID"] = $campaign_preview_id;
	       	$InsertionOrderPreviewFactory = \_factory\InsertionOrderPreview::get_instance();
	       	$_InsertionOrderPreview = $InsertionOrderPreviewFactory->get_row($params);
	       	$InsertionOrderPreview->InsertionOrderID 	= $_InsertionOrderPreview->InsertionOrderID;
	    endif;

	    // else new campaign, ispreview is always true

	    $InsertionOrderPreview->UserID             			= $this->auth->getEffectiveUserID();

    	$InsertionOrderPreview->Name                      = $campaignname;
    	$InsertionOrderPreview->StartDate                 = date("Y-m-d H:i:s", strtotime($startdate));
    	$InsertionOrderPreview->EndDate                   = date("Y-m-d H:i:s", strtotime($enddate));
    	$InsertionOrderPreview->Customer                  = $customername;
    	$InsertionOrderPreview->CustomerID                = $customerid;
    	$InsertionOrderPreview->ImpressionsCounter        = 0;
    	$InsertionOrderPreview->MaxImpressions            = $maximpressions;
    	$InsertionOrderPreview->CurrentSpend              = 0;
    	$InsertionOrderPreview->MaxSpend                  = $maxspend;
    	$InsertionOrderPreview->Active                    = 1;
    	$InsertionOrderPreview->DateCreated               = date("Y-m-d H:i:s");
    	$InsertionOrderPreview->DateUpdated               = date("Y-m-d H:i:s");
    	$InsertionOrderPreview->ChangeWentLive            = 0;

	    $InsertionOrderPreviewFactory = \_factory\InsertionOrderPreview::get_instance();
	    $new_campaign_preview_id = $InsertionOrderPreviewFactory->saveInsertionOrderPreview($InsertionOrderPreview);

	    if (!$this->is_super_admin && $new_campaign_preview_id !== null && $this->config_handle['mail']['subscribe']['campaigns'] === true):
	    
		    // if this ad campaign was not created/edited by the admin, then send out a notification email
		    $message = '<b>NginAd Demand Customer Campaign Added by ' . $this->true_user_name . '.</b><br /><br />';
		    $message = $message.'<table border="0" width="10%">';
		    $message = $message.'<tr><td><b>InsertionOrderID: </b></td><td>'.$new_campaign_preview_id.'</td></tr>';
		    $message = $message.'<tr><td><b>UserID: </b></td><td>'.$InsertionOrderPreview->UserID.'</td></tr>';
		    $message = $message.'<tr><td><b>Name: </b></td><td>'.$InsertionOrderPreview->Name.'</td></tr>';
		    $message = $message.'<tr><td><b>StartDate: </b></td><td>'.$InsertionOrderPreview->StartDate.'</td></tr>';
		    $message = $message.'<tr><td><b>EndDate: </b></td><td>'.$InsertionOrderPreview->EndDate.'</td></tr>';
		    $message = $message.'<tr><td><b>Customer: </b></td><td>'.$InsertionOrderPreview->Customer.'</td></tr>';
		    $message = $message.'<tr><td><b>CustomerID: </b></td><td>'.$InsertionOrderPreview->CustomerID.'</td></tr>';
		    $message = $message.'<tr><td><b>MaxImpressions: </b></td><td>'.$InsertionOrderPreview->MaxImpressions.'</td></tr>';
		    $message = $message.'<tr><td><b>MaxSpend: </b></td><td>'.$InsertionOrderPreview->MaxSpend.'</td></tr>';
		    $message = $message.'</table>';
		    	
		    $subject = "NginAd Demand Customer Campaign Added by " . $this->true_user_name;
		    
		    $transport = $this->getServiceLocator()->get('mail.transport');
		    
		    $text = new Mime\Part($message);
		    $text->type = Mime\Mime::TYPE_HTML;
		    $text->charset = 'utf-8';
		    
		    $mimeMessage = new Mime\Message();
		    $mimeMessage->setParts(array($text));
		    $zf_message = new Message();
		    
		    $zf_message->addTo($this->config_handle['mail']['admin-email']['email'], $this->config_handle['mail']['admin-email']['name'])
		    ->addFrom($this->config_handle['mail']['reply-to']['email'], $this->config_handle['mail']['reply-to']['name'])
		    ->setSubject($subject)
		    ->setBody($mimeMessage);
		    $transport->send($zf_message);
		    
	    endif;
	    
		$refresh_url = "/demand/?ispreview=true";
		$viewModel = new ViewModel(array('refresh_url' => $refresh_url));

		return $viewModel->setTemplate('dashboard-manager/demand/interstitial.phtml');

	}

	/*
	 * END NGINAD InsertionOrder Actions
	*/

	/*
	 * BEGIN NGINAD Helper Methods
	*/

	/**
	 * 
	 * @param unknown $campaign_id
	 * @param unknown $campaign_preview_id
	 * @param unknown $is_preview
	 * @return multitype:NULL
	 */
	private function getBreadCrumbInfoFromInsertionOrder($campaign_id, $campaign_preview_id, $is_preview) {

			if ($is_preview == true):
				return $this->getBreadCrumbInfoFromCampaignPreviewID($campaign_preview_id);
			else:
				return $this->getBreadCrumbInfoFromCampaignID($campaign_id);
			endif;
	}

	/**
	 * 
	 * @param unknown $banner_id
	 * @param unknown $banner_preview_id
	 * @param unknown $is_preview
	 */
	private function getBreadCrumbInfoFromBanner($banner_id, $banner_preview_id, $is_preview) {

			if ($is_preview == true):
				return $this->getBreadCrumbInfoFromInsertionOrderLineItemPreviewID($banner_preview_id);
			else:
				return $this->getBreadCrumbInfoFromInsertionOrderLineItemID($banner_id);
			endif;
	}

	/**
	 * 
	 * @param unknown $id
	 * @return unknown
	 */
	private function getBreadCrumbInfoFromInsertionOrderLineItemID($id) {

		$InsertionOrderLineItemFactory = \_factory\InsertionOrderLineItem::get_instance();
		$params = array();
		$params["InsertionOrderLineItemID"] = $id;

		$InsertionOrderLineItem = $InsertionOrderLineItemFactory->get_row($params);

		$bread_crumb_info = $this->getBreadCrumbInfoFromCampaignID($InsertionOrderLineItem->InsertionOrderID);
		$bread_crumb_info["BCBanner"] = $InsertionOrderLineItem->Name;

		return $bread_crumb_info;

	}

	/**
	 * 
	 * @param unknown $id
	 * @return unknown
	 */
	private function getBreadCrumbInfoFromInsertionOrderLineItemPreviewID($id) {

		$InsertionOrderLineItemPreviewFactory = \_factory\InsertionOrderLineItemPreview::get_instance();
		$params = array();
		$params["InsertionOrderLineItemPreviewID"] = $id;

		$InsertionOrderLineItemPreview = $InsertionOrderLineItemPreviewFactory->get_row($params);

		$bread_crumb_info = $this->getBreadCrumbInfoFromCampaignPreviewID($InsertionOrderLineItemPreview->InsertionOrderPreviewID);
		$bread_crumb_info["BCBanner"] = $InsertionOrderLineItemPreview->Name;

		return $bread_crumb_info;

	}

	/**
	 * 
	 * @param unknown $id
	 * @return multitype:NULL
	 */
	private function getBreadCrumbInfoFromCampaignID($id) {

		$InsertionOrderFactory = \_factory\InsertionOrder::get_instance();
		$params = array();
		$params["InsertionOrderID"] = $id;

		$InsertionOrder = $InsertionOrderFactory->get_row($params);

		return array("BCInsertionOrder"=>'<a href="/demand/viewlineitem/' . $InsertionOrder->InsertionOrderID . '">' . $InsertionOrder->Name . "</a>");

	}

	/**
	 * 
	 * @param unknown $id
	 * @return multitype:NULL
	 */
	private function getBreadCrumbInfoFromCampaignPreviewID($id) {

		$InsertionOrderPreviewFactory = \_factory\InsertionOrderPreview::get_instance();
		$params = array();
		$params["InsertionOrderPreviewID"] = $id;

		$InsertionOrderPreview = $InsertionOrderPreviewFactory->get_row($params);

		return array("BCInsertionOrder"=>'<a href="/demand/viewlineitem/' . $InsertionOrderPreview->InsertionOrderPreviewID . '?ispreview=true">' . $InsertionOrderPreview->Name . "</a>");

	}

	/*
	 * END NGINAD Helper Methods
	*/

}
?>