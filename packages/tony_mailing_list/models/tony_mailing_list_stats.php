<?php  

defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('user_attributes');  

class TonyMailingListStats {
	
	public $errorMsg="";
	
	
	public static function calculateStats($mailing){ 
		
		$calcStats=array();
		
		$stats = unserialize($mailing->getStats());
		
		//make sure there's a valid stats object, and that stats were enabled on this mailing 
		if(!is_array($stats) || !$stats['statsEnabled']) 
			return $calcStats; 
		
		$sent = $mailing->getSentCount(); 
		
		$calcStats['sent'] = intval($sent);
		
		$calcStats['opened'] = intval($stats['opened']);
		$calcStats['openedPercent'] = ($sent) ? round(intval($stats['opened'])/$sent*100) : 0;
		
		$calcStats['viewedOnly'] = intval($stats['opened']) - intval($stats['clickThruUsers']) - intval($stats['unsubscribed']);
		$calcStats['viewedOnlyPercent'] = ($sent) ? round($calcStats['viewedOnly']/$sent*100) : 0;
			
		$calcStats['clickThrus'] = intval($stats['clickThruUsers']);
		$calcStats['clickThrusPercent'] = ($sent) ? round($calcStats['clickThrus']/$sent*100) : 0;
		
		$calcStats['unopened'] = intval($sent - $calcStats['opened']);
		$calcStats['unopenedPercent'] = ($sent) ? round($calcStats['unopened']/$sent*100) : 0;
		
		$calcStats['unsubscribed'] = intval($stats['unsubscribed']);
		$calcStats['unsubscribedPercent'] = ($sent) ? round($calcStats['unsubscribed']/$sent*100) : 0;
		
		return $calcStats;
	}
	
	
	public function trackHit($data){ 
		
		$mlmID = intval($data['mlm']);
		$uID = intval($data['u']);
		$mluID = intval($data['mlu']);
		$url = trim($data['url']);
		$manageSubscriptions = intval($data['uns']);
		
		if( $mlmID  ){
			
			//get mailing
			$mailing = TonyMailingListMailing::getById( $mlmID ); 
			if(!$mailing){
				$this->errorMsg=t('mailing not found');
				return false; 
			}
			
			$stats = $mailing->getStatsObj();
			
			if( !$this->isValidUser( $mailing, $stats, $uID, $mluID) ) 
				return false; 
			
			//see if user's been tracked already
			if( !$this->userTracked ){ 
				$stats['opened']++; 
				
				if($uID) $stats['trackedUIDs'][]=$uID;
				if($mluID) $stats['trackedMLUIDs'][]=$mluID;
			}
			
			//track link clicks 
			if( $url && !$manageSubscriptions ){ 
				$linkData=array();
				$linkIndex=-1;
				
				//test to see if link exists in email
				$emailHTML = TonyMailingList::getHeaderHTML().$mailing->getBody().TonyMailingList::getFooterHTML(); 
				if( !stristr($emailHTML,$url) ){
					$this->errorMsg="link not found in email";
					return; 
				}
				
				if( is_array($stats['clickThruUserUIDs']) && in_array($uID,$stats['clickThruUserUIDs']) ) 
					$userClickThruTracked=1;
				if( is_array($stats['clickThruUserMLUIDs']) && in_array($mluID,$stats['clickThruUserMLUIDs']) ) 
					$userClickThruTracked=1;
				
				if( !$userClickThruTracked ){ 
					//is this user clicking on a link for the first time? 
					$stats['clickThruUsers']++;	
					
					if($uID) $stats['clickThruUserUIDs'][]=$uID;
					if($mluID) $stats['clickThruUserMLUIDs'][]=$mluID;				
				}		
				
				//does this link already exist
				for($i=0;$i<count($stats['clickedLinks']);$i++){
					if( $stats['clickedLinks'][$i]['url']==$url ){
						$linkIndex=$i;
						$linkData=$stats['clickedLinks'][$i]; 
					}
				}
				
				//add link data record if it doesn't exist
				if($linkIndex<0){
					$linkData=array( 'url'=>$url, 'mluIDs'=>array(), 'uIDs'=>array() ); 
					$stats['clickedLinks'][]=$linkData;
					$linkIndex=count($stats['clickedLinks'])-1;
				}
				
				//has this user clicked on this link already? 
				if( in_array($mluID,$linkData['mluIDs']) ) $linkTracked=1;
				if( in_array($uID,$linkData['uIDs']) ) $linkTracked=1;
				
				if( !$linkTracked ){
					$linkData['clickThrus']++;
				
					if($uID) $linkData['uIDs'][]=$uID;
					if($mluID) $linkData['mluIDs'][]=$mluID;
				}
				
				$stats['clickedLinks'][$linkIndex]=$linkData;
			}
			
			$mailing->setStats( serialize($stats) );
			
			$mailing->save(); 
			
		}
		
	}
	
	
	public function trackUnsubscribe( $mlmID, $mluID, $uID ){
		
		if( !intval($mlmID) || (!$mluID && !$uID) ){
			$this->errorMsg=t('Invalid mailing or user ids');
			return false; 
		}
			
		//get mailing
		$mailing = TonyMailingListMailing::getById( $mlmID ); 
		if(!$mailing){
			$this->errorMsg=t('mailing not found');
			return false; 
		}
		
		$stats = $mailing->getStatsObj(); 
		
		if(!$this->isValidUser( $mailing, $stats, $uID, $mluID)) 
			return false; 
	
		if( intval($uID) && in_array($uID,$stats['unsubscribedUIDs']) ){
			$this->errorMsg=t('unsubscribed user already tracked');
			return false; 
		}elseif( intval($mluID) && in_array($mluID,$stats['unsubscribedMLUIDs']) ){
			$this->errorMsg=t('unsubscribed non-registered user already tracked');
			return false;
		}
		
		if(intval($mluID)) $stats['unsubscribedMLUIDs'][]=intval($mluID);
		if(intval($uID)) $stats['unsubscribedUIDs'][]=intval($uID);
	
		$stats['unsubscribed']++;
		
		$mailing->setStats( serialize($stats) );
		
		$mailing->save();
		
	}
	
	public function isValidUser($mailing,$stats,$uID,$mluID){
		
		if( $uID ){ 
			$uIDs= $mailing->getSentUIDsArray();
			
			//is this a valid user? 
			if( !in_array($uID,$uIDs) ){
				$this->errorMsg=t('user not in mailing');
				return false;
			}
			
			//has this user been tracked already? 
			if( in_array($uID, $stats['trackedUIDs']) ) $this->userTracked=1;				
			
		}elseif($mluID){ 
			$mlUIDs = $mailing->getSentMLUIDsArray();
			
			//is this a valid unregistered user? 
			if( !in_array($mluID,$mlUIDs) ){
				$this->errorMsg=t('unregistered user not in mailing');
				return false;
			}
			
			//has this unregistered user been tracked already? 
			if( in_array($mluID,$stats['trackedMLUIDs']) ) $this->userTracked=1;
			
		}else{
			//no user id found
			$this->errorMsg=t('user not found');
			return false;	
		}			
			
		return true; 
	}
	
	
	public static function getChartColors(){ 
		return array('4995d0','5dc0b8','6468bc','2c60a9');
	}
	
	public static function getChartLabels(){ 
		return array(t("Viewed Only"),t("Click-Thrus"),t("Unsubscribed"),t("Unopened*"));
	}	
	
	public static function getChart($calculatedStats){
		
		//chart type & size 
		$chartImg = "http://chart.apis.google.com/chart?cht=p3";
					
		$chartImg .= "&chs=170x115";
		
		$chartImg .= "&chma=0,0,0,0|0,0";
		
		//$chartImg .= "&chdl=".join('|',self::getChartLabels()); 
		
		$chartImg .= "&chd=t:".intval($calculatedStats['viewedOnly']).",".
							   intval($calculatedStats['clickThrus']).",".
							   intval($calculatedStats['unsubscribed']).",".
							   intval($calculatedStats['unopened']); 
		
		$chartImg .= "&chp=0";
		
		$chartImg .= "&chco=".join(',',self::getChartColors());
			
		return $chartImg;
			
	}
	
	public static function printSpacerImg(){
		
		$pkg = Package::getByHandle('tony_mailing_list'); 
		$imgPath = $pkg->getPackagePath().'/images/spacer.gif';
		if(!file_exists($imgPath)) throw new Exception('image not found!');
		$filesize=filesize($imgPath); 	 
		header("Pragma: public"); 
		header("Cache-Control: no-cache, must-revalidate");				
		header('Content-Type: image/gif');
		header('Content-Length: '.$filesize); 
		header("Content-Disposition: inline; filename=spacer.gif");
		header("Content-Transfer-Encoding: binary");
		
		$fp = fopen($imgPath, 'r'); 
		$file_buffer = fread($fp, $filesize); 
		fclose ($fp);			
		print $file_buffer;
	}
	
} 

?>