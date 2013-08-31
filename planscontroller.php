<?php
class PlansController extends AppController{

	public $name = "Plans";
	public $uses = array('Plan','User');
	public $components = array(
		'Email',
	);
/**
 * beforeFilter
 *
 * @return void
 * @access public
 */
	public function beforeFilter() {
		parent::beforeFilter();
	}


	public function index() 
	{
		$this->set('title_for_layout', __('Create plan'));
		$this->layout = 'admin';
	}
	public function admin_index()
	{
		$this->set('title_for_layout', __('Manage plan'));
		$this->layout = 'admin';
		$this->paginate = array(
									'limit' => 5,
									'order' => array('id' => 'DESC')
								);				
		$ListPlan = $this->paginate('Plan');
		$this->set(compact('ListPlan'));
	}
	public function admin_add_plan()
	{
		$this->set('title_for_layout', __('Add plan'));
		$this->layout = 'admin';
		if($this->request->is('post'))
		{
			$this->request->data['Plan']['size']=$this->request->data['Plan']['size'];
			$this->request->data['Plan']['payment_monthly']=$this->request->data['Plan']['payment_monthly'];
			$this->request->data['Plan']['payment_yearly']=$this->request->data['Plan']['payment_yearly'];
			$this->request->data['Plan']['mail_limit']=$this->request->data['Plan']['mail_limit'];
			$this->request->data['Plan']['subscriber_limit']=$this->request->data['Plan']['subscriber_limit'];
			$this->Plan->set($this->request->data);
			if($this->Plan->validates())
			{
				$this->Plan->create();
				$this->Plan->save($this->request->data);
				$this->request->data=null;
				$this->Session->setFlash('Plan created successfully.'); 
				$this->redirect(array('action' => 'index'));
			}
		}
	}
	public function admin_edit_plan($planId = 0)
	{
		$this->set('title_for_layout', __('Edit plan'));
		$this->layout = 'admin';
		if($this->request->is('post')&& $planId)
		{
			$this->request->data['Plan']['size']=$this->request->data['Plan']['size'];
			$this->request->data['Plan']['payment_monthly']=$this->request->data['Plan']['payment_monthly'];
			$this->request->data['Plan']['payment_yearly']=$this->request->data['Plan']['payment_yearly'];
			$this->request->data['Plan']['mail_limit']=$this->request->data['Plan']['mail_limit'];
			$this->request->data['Plan']['subscriber_limit']=$this->request->data['Plan']['subscriber_limit'];
			$this->Plan->set($this->request->data);
			if($planId == 9)
			{		
				if($this->Plan->validates(array('fieldList' => array('payment_monthly','payment_yearly','mail_limit','subscriber_limit'))))
				{
					$this->Plan->id = $planId;
					$this->Plan->save($this->request->data);
					$this->request->data=null;
					$this->Session->setFlash('Plan updated successfully.');
					$this->redirect(array('action' => 'index'));
				}
			}
			else
			{
				if($this->Plan->validates())
				{
					$size = explode('-', $this->request->data['Plan']['size']);
					if($size[1]>=$size[0])
					{
						$this->Plan->id = $planId;
						$this->Plan->save($this->request->data);
						$this->request->data=null;
						$this->Session->setFlash('Plan updated successfully.');
						$this->redirect(array('action' => 'index'));
					}
					else
					{
						$this->Plan->validationErrors = array('size' => array('second parameter must be grater than first'));
					}
				}
			}
		}
		else if(!$planId)
		{
			$this->Session->setFlash('You are tring to update wrong plan.'); 
			$this->redirect(array('action' => 'index'));
		}
		$plan = $this->Plan->find('all',array('conditions' => array('Plan.id' => $planId)));
		$this->set(compact('plan','planId'));
	}	
	public function admin_delete_plan($planId=0)
	{
		$this->set('title_for_layout', __('Delete contact'));
		$this->layout = 'admin';
		if($planId!=0)
		{
			//don't delete default plan
			if($planId == 9)
			{
				$this->Session->setFlash("You cann't delete default plan.");	
			}
			else 
			{
				$this->Plan->recursive = 0;
				$this->Plan->delete($planId);
				$this->loadModel('PlanImage');
				$planImageData = $this->PlanImage->find('all',array('conditions' => array('PlanImage.plan_id' => $planId)));
				if(count($planImageData))
				{
					$this->PlanImage->delete(array('conditions' => array('PlanImage.plan_id' => $planId)));
				}
				$this->Session->setFlash("Plan deleted successfully."); 
			}
		}
		else 
		{
			$this->Session->setFlash("You are tring to delete wrong Plan."); 
		}
		$this->redirect(array('action' => 'index'));
	}
	public function admin_plan_status($planId=0)
	{
		$this->set('title_for_layout', __('Plan Status'));
		$this->layout = 'admin';
		if($planId!=0)
		{
			if($planId == 9)
			{
				$this->Session->setFlash("You cann't change status of default plan.");	
			}
			else 
			{
				$this->Plan->recursive = 0;
				$plan = $this->Plan->find('all',array('conditions' => array('Plan.id' => $planId)));
				$currentStatus = $plan[0]['Plan']['status'];
				if($currentStatus=='active')
				{
					$this->request->data['Plan']['status']="inactive";
				}
				else
				{
					$this->request->data['Plan']['status']="active";
				}
				$this->Plan->set($this->request->data);
				$this->Plan->id = $planId;
				$this->Plan->save($this->request->data);
				$this->Session->setFlash("Plan status successfully changed.");
			}
		}
		else 
		{
			$this->Session->setFlash("You are tring to change wrong Plan status."); 
		}
		$this->redirect(array('action' => 'index'));
	}
	public function admin_imgplan()
	{
		$this->set('title_for_layout', __('Manage image storage'));
		$this->layout = 'admin';
		$this->loadModel('PlanImage');
		$this->PlanImage->recursive =1;
		$this->paginate = array(
									'limit' => 6,
									'order' => array('PlanImage.plan_id' => 'DESC')
								);				
		$ListPlanImg = $this->paginate('PlanImage');
		$this->set(compact('ListPlanImg'));
	}
	public function admin_add_imgplan()
	{
		$this->set('title_for_layout', __('Add images store for plan'));
		$this->layout = 'admin';
		if($this->request->is('post'))
		{
			$this->loadModel('PlanImage');
			$this->request->data['PlanImage']['plan_id']=$this->request->data['PlanImage']['plan_id'];
			$this->request->data['PlanImage']['price_percentage']=$this->request->data['PlanImage']['price_percentage'];
			$this->request->data['PlanImage']['price_label']=$this->request->data['PlanImage']['price_label'];
			$this->request->data['PlanImage']['size_in_mb']=$this->request->data['PlanImage']['size_in_mb'];
			$this->PlanImage->set($this->request->data);
			$this->PlanImage->recursive =0;
			if($this->PlanImage->validates())
			{
				$this->PlanImage->create();
				$this->PlanImage->save($this->request->data);
				$this->request->data=null;
				$this->Session->setFlash('Plan image store created successfully.'); 
				$this->redirect(array('action' => 'imgplan'));
			}
		}
		$planId = $this->Plan->find('all',array('order' => array('Plan.id' =>'DESC')));
		$planIdArray = array();
		if(count($planId))
		{
			foreach ($planId as $plan)
			{
				$planIdArray[$plan['Plan']['id']] = $plan['Plan']['size'];
			}
		}
		$this->set(compact('planIdArray'));
	}
	public function admin_edit_imgplan($imageStoreId = 0)
	{
		$this->set('title_for_layout', __('Edit images store for plan'));
		$this->layout = 'admin';
		$this->loadModel('PlanImage');
		if($this->request->is('post')&&$imageStoreId)
		{
			$this->loadModel('PlanImage');
			$this->request->data['PlanImage']['plan_id']=$this->request->data['PlanImage']['plan_id'];
			$this->request->data['PlanImage']['price_percentage']=$this->request->data['PlanImage']['price_percentage'];
			$this->request->data['PlanImage']['price_label']=$this->request->data['PlanImage']['price_label'];
			$this->request->data['PlanImage']['size_in_mb']=$this->request->data['PlanImage']['size_in_mb'];
			$this->PlanImage->set($this->request->data);
			$this->PlanImage->recursive =0;
			if($this->PlanImage->validates())
			{
				$this->PlanImage->id = $imageStoreId;
				$this->PlanImage->save($this->request->data);
				$this->request->data=null;
				$this->Session->setFlash('Plan image store updated successfully.'); 
				$this->redirect(array('action' => 'imgplan'));
			}
		}
	    $planId = $this->Plan->find('all',array('order' => array('Plan.id' =>'DESC')));
		$planIdArray = array();
		if(count($planId))
		{
			foreach ($planId as $plan)
			{
				$planIdArray[$plan['Plan']['id']] = $plan['Plan']['size'];
			}
		}
		$planImg = $this->PlanImage->find('all',array('conditions' => array('PlanImage.id' => $imageStoreId)));
		$defaultSel = $planImg[0]['PlanImage']['plan_id'];
		$this->set(compact('planImg','imageStoreId','planIdArray','defaultSel'));
	}
	public function admin_delete_imgplan($imageStoreId=0)
	{
		$this->set('title_for_layout', __('Delete images store for plan'));
		$this->layout = 'admin';
		$this->loadModel('PlanImage');
		if($imageStoreId!=0)
		{
			$this->PlanImage->recursive = 0;
			$this->PlanImage->delete($imageStoreId);
			$this->Session->setFlash("Plan deleted successfully."); 
		}
		else 
		{
			$this->Session->setFlash("You are tring to delete wrong images store."); 
		}
		$this->redirect(array('action' => 'imgplan'));
	}
	public function upgrade()
	{
		$this->set('title_for_layout', __('Upgrade your Account'));
		$this->layout = 'admin';
		$userId = $this->Auth->user('id');
		$this->loadModel('User');
		$this->loadModel('UserPlanRelation');
		$this->loadModel('Plan');
		$remainingDays = "";
		if($this->request->is('post'))
		{

			$PlanId 		  = $this->request->data['Plan']['PlanId'];
			$planImage        = $this->request->data['Plan']['planImage'];
			$plan_price       = $this->request->data['Plan']['plan_price'];
			$plan_image_price = $this->request->data['Plan']['plan_image_price'];	
			$subtotal		  = $this->request->data['Plan']['subtotal'];
			$planImageCoupon  = $this->request->data['Plan']['planImageCoupon'];
			$coupon_valid	  = $this->request->data['Plan']['coupon_valid'];
			$discount		  = $this->request->data['Plan']['discount'];	
			$PlanTotal  	  = $this->request->data['Plan']['subtotal'];
			if($coupon_valid =="yes")
			{
				$subtotal    = $subtotal+$discount;
			}
			$plan_type  	  = $this->request->data['Plan']['plan_type'];
			$expired = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "+1 month" ) );
			if($plan_type == "yearly")
			{
				$PlanTotal    = $PlanTotal*12;
				$expired = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "+12 month" ) );
			}
			foreach ($planImage as $value) 
			{
				if($value!='')
				{
					$planImage = $value;
					break;
				}
			}
			$this->request->data['UserPlanRelation']['user_id']=$userId;
			$this->request->data['UserPlanRelation']['plan_id']=$PlanId;
			$this->request->data['UserPlanRelation']['plan_image_id']=$planImage;
			$this->request->data['UserPlanRelation']['plan_price']=$plan_price;	
			$this->request->data['UserPlanRelation']['plan_image_price']=$plan_image_price;
			$this->request->data['UserPlanRelation']['subtotal']=$subtotal;
			$this->request->data['UserPlanRelation']['coupon_code']=$planImageCoupon;
			$this->request->data['UserPlanRelation']['coupon_valid']=$coupon_valid;			
			$this->request->data['UserPlanRelation']['discount']=$discount;
			$this->request->data['UserPlanRelation']['total']=$PlanTotal;
			$this->request->data['UserPlanRelation']['plan_type']=$plan_type;
			$this->request->data['UserPlanRelation']['expired']=$expired;
			$this->UserPlanRelation->set($this->request->data);
			$this->UserPlanRelation->create();
			$this->UserPlanRelation->save($this->request->data);
			$UserPlanRelation_id = $this->UserPlanRelation->getLastInsertID();
			$this->request->data=null;
			$this->Session->write('UserPlanRelation_id',$UserPlanRelation_id);
			$this->Session->setFlash("Plan data saved.You are redirecting to paypal for payment process...........Please don't get logout"); 
			$this->redirect(array('action' => 'billing'));
		}
		$currentPlan = $this->UserPlanRelation->find('all', array('conditions' => array('UserPlanRelation.user_id' =>$userId,'UserPlanRelation.status' => 'active')));
		if(count($currentPlan))
		{
			$default_plan_id = $currentPlan[0]['UserPlanRelation']['plan_id'];
			$created = date('Y-m-d',strtotime($currentPlan[0]['UserPlanRelation']['created']));
			$expired = date('Y-m-d',strtotime($currentPlan[0]['UserPlanRelation']['expired']));
			$daylen = 60*60*24;
			$remainingDays = (strtotime($expired)-strtotime($created)-$daylen)/$daylen;
			$currentPlan  = $this->Plan->find('all', array('conditions' => array('Plan.status' =>'active'), 'conditions' => array('Plan.id' => $default_plan_id)));
			$default_plan = $currentPlan[0]['Plan']['size'];
		}
		else
		{
			$userCreatedDate = $this->User->find('all',array('fields'=>'created','conditions' =>array('User.id' => $userId)));
			$createdDate = date('Y-m-d',strtotime($userCreatedDate[0]['User']['created']));
			$currDate = date('Y-m-d');
			
			$daylen = 60*60*24;
			$remainingDays = 30-(strtotime($currDate)-strtotime($createdDate))/$daylen;
			$default_plan = "just getting started";
		}
		$this->Plan->recursive= 2;
		$Plans = $this->Plan->find('all', array('conditions' => array('Plan.status' =>'active'),'order' => array('Plan.id' => 'ASC')));
		$this->set(compact('currentPlan','default_plan','Plans','remainingDays'));
	}
	public function billing()
	{
		$this->set('title_for_layout', __('Billing Information'));
		$this->layout = 'admin';
		$this->loadModel('UserPlanRelation');
		if($this->Session->check('UserPlanRelation_id'))
		{
			$userPlanRelationId = $this->Session->read('UserPlanRelation_id');
			$this->Session->write('UserPlanRelation_id_md',md5($userPlanRelationId));
		}
		else
		{
			$userPlanRelationId = 0;
		}
		$savedResult = $this->UserPlanRelation->find('all',array('conditions' => array('UserPlanRelation.id' => $userPlanRelationId)));
		$this->set(compact('savedResult'));
	}
	public function confirmation($id = 0)
	{
		$this->set('title_for_layout', __('Payment Confirmation'));
		$this->layout = 'admin';
		if($this->Session->check('UserPlanRelation_id_md'))
		{
			$this->loadModel('UserPlanRelation');
			$UserPlanRelation_id_md = $this->Session->read('UserPlanRelation_id_md');
			$UserPlanRelation_id    = $this->Session->read('UserPlanRelation_id');
			if($id == $UserPlanRelation_id_md)
			{
				$checkAlreadyUpdateData = $this->UserPlanRelation->find('first',array('conditions'=>array('UserPlanRelation.id'=>$UserPlanRelation_id)));
				if($checkAlreadyUpdateData['UserPlanRelation']['status'] != "active")
				{
					$userId = $this->Auth->user('id');
					//deactive previous plan but store any active plan remaining mails
					$previousData = $this->UserPlanRelation->find('all',array('conditions'=>array('UserPlanRelation.user_id'=>$userId)));
					if(count($previousData))
					{
						foreach ($previousData as $row)
						{
							$planStatus 	 = $row['UserPlanRelation']['status'];
							$planType   	 = $row['UserPlanRelation']['plan_type'];
							$planId     	 = $row['UserPlanRelation']['plan_id'];
							$planRelationId  = $row['UserPlanRelation']['id'];
							$planExpired     = $row['UserPlanRelation']['expired'];
							$Expired     = $row['UserPlanRelation']['expired'];
							$todayDate 		 = date("Y-m-d");
							$todayDate = date("Y-m-d",strtotime($todayDate));
							$planExpired = date("Y-m-d",strtotime($planExpired));
							$diff = ((strtotime($planExpired)-strtotime($todayDate))/(60 * 60 * 24));	
							
							if($planStatus =="active" && $diff >0)
							{
								$userData  = $this->User->find('first',array('conditions'=>array('User.id'=>$userId)));
								if(count($userData))
								{
									$usedEmail = $userData['User']['queue_email'];
									$this->loadModel('Plan');
									$planData  = $this->Plan->find('first',array('conditions'=>array('Plan.id'=>$planId)));
									if(count($planData))
									{
										//we need to work for yearly plan limit because our email limit system is monthly. so for finding remaining email for yearly plan use following calculation
										if($planType=="yearly")
										{
											//check month is starting if yes then reduce 1
											$remainingMonth = intval($diff/30); 
											if(($diff%30)==0)
											{
												$remainingMonth = $remainingMonth-1;
											}
											//also check it's not last month
											if($remainingMonth>=1)
											{
												$planMailLimit = $planData['Plan']['mail_limit']+($planData['Plan']['mail_limit']*$remainingMonth);
											}
											else 
											{
												$planMailLimit = $planData['Plan']['mail_limit'];
											}
											$remainingMail = $planMailLimit-$usedEmail;
										}
										else 
										{
											$planMailLimit = $planData['Plan']['mail_limit'];
											$remainingMail = $planMailLimit-$usedEmail;
										}
										//save previous plan history before we go to deactivate it
										$this->loadModel('UserPlanPreviousMail');
										$this->request->data['UserPlanPreviousMail']['user_id'] = $userId;
										$this->request->data['UserPlanPreviousMail']['user_plan_relation_id'] = $planRelationId;
										$this->request->data['UserPlanPreviousMail']['remaining_limit'] = $remainingMail;
										$this->request->data['UserPlanPreviousMail']['expired'] = $Expired;
										$this->UserPlanPreviousMail->create();
										$this->UserPlanPreviousMail->save($this->request->data);
										$UserPlanPreviousMailId = $this->UserPlanPreviousMail->getLastInsertID();
											
										//save overall pervous plan email remaining limit
										$this->loadModel('UserPlanPreviousMailTotal');
										$UserPlanPreviousMailData   = $this->UserPlanPreviousMailTotal->find('first',array('conditions'=>array('UserPlanPreviousMailTotal.user_id'=>$userId)));
										if(count($UserPlanPreviousMailData))
										{
											//update
											$total 			 = $UserPlanPreviousMailData['UserPlanPreviousMailTotal']['total'];
											$remaining 		 = $UserPlanPreviousMailData['UserPlanPreviousMailTotal']['remaining'];
											$previousMailIds = $UserPlanPreviousMailData['UserPlanPreviousMailTotal']['previous_mail_ids'];
											//reset data
											$total 			 = $total +  $remainingMail;
											$remaining 		 = $remaining +  $remainingMail;
											if($previousMailIds!="")
											{
												$previousMailIdsArray   = explode(",", $previousMailIds);
												$previousMailIdsArray[] = $UserPlanPreviousMailId;
												$previousMailIds = implode(",", $previousMailIdsArray);
											}
											else
											{
												$previousMailIds = $UserPlanPreviousMailId;
											}
											$this->request->data['UserPlanPreviousMailTotal']['user_id'] = $userId;
											$this->request->data['UserPlanPreviousMailTotal']['total'] = $total;
											$this->request->data['UserPlanPreviousMailTotal']['remaining'] = $remaining;
											$this->request->data['UserPlanPreviousMailTotal']['previous_mail_ids'] = $previousMailIds;
											$this->UserPlanPreviousMailTotal->id = $UserPlanPreviousMailData['UserPlanPreviousMailTotal']['id'];
											$this->UserPlanPreviousMailTotal->save($this->request->data);
										}
										else
										{
											//insert
											$this->request->data['UserPlanPreviousMailTotal']['user_id'] = $userId;
											$this->request->data['UserPlanPreviousMailTotal']['total'] = $remainingMail;
											$this->request->data['UserPlanPreviousMailTotal']['used'] = 0;
											$this->request->data['UserPlanPreviousMailTotal']['remaining'] = $remainingMail;
											$this->request->data['UserPlanPreviousMailTotal']['previous_mail_ids'] = $UserPlanPreviousMailId;
											$this->UserPlanPreviousMailTotal->create();
											$this->UserPlanPreviousMailTotal->save($this->request->data);
										}
									}
								}
									
							}
							$this->request->data['UserPlanRelation']['status'] = "inactive";
							$this->UserPlanRelation->id = $row['UserPlanRelation']['id'];
							$this->UserPlanRelation->save($this->request->data);
						}
					}
					//activate current plan
					$this->request->data['UserPlanRelation']['status'] = "active";
					$this->UserPlanRelation->id = $UserPlanRelation_id;
					$this->UserPlanRelation->save($this->request->data);
					//user account update and set trial to 1
					$this->loadModel('User');
					$this->request->data['User']['trail'] = 0;
					$this->request->data['User']['payment'] = 1;
					$this->request->data['User']['queue_email'] = 0;
					$current = date("Y-m-d");
					$this->request->data['User']['queue_email_date_update'] = $current;
					$this->User->id = $userId;
					$this->User->save($this->request->data);

					$message ="<p>Congratulations! You successfully upgraded your account.</p>";
					$recieveremail = $this->Auth->user('email');
					$subject = "Successfully upgraded plan";
					//$this->sendPaymentMail($subject,$recieveremail,$message);
					$this->Session->setFlash('Payment successfully received. And current plan activated as default.'); 
				}
				else 
				{
					 $this->Session->setFlash('Payment status already updated.'); 
				}
			}
		}
		else
		{
			$this->Session->setFlash('Session expired. We are unable to update your status.please contact into@netleon.com'); 
		}
	}
	public function cancel($id = 0)
	{
		$this->set('title_for_layout', __('Paypal payment cancelled'));
		$this->layout = 'admin';
		if($this->Session->check('UserPlanRelation_id_md'))
		{
			$this->loadModel('UserPlanRelation');
			$UserPlanRelation_id_md = $this->Session->read('UserPlanRelation_id_md');
			$UserPlanRelation_id    = $this->Session->read('UserPlanRelation_id');
			if($id == $UserPlanRelation_id_md)
		    {
		    	$this->request->data['UserPlanRelation']['status'] = "inactive";
				$this->UserPlanRelation->id = $UserPlanRelation_id;
				$this->UserPlanRelation->save($this->request->data);
				
				$message ="<p>We are unable to upgrade your plan. Because You have cancelled payment request from paypal payments.</p>";
				$recieveremail = $this->Auth->user('email');
				$subject = "Plan not upgraded";
				$this->sendPaymentCancelMail($subject,$recieveremail,$message);
				
		    }
		}
	}	
}
