<?php
class UserPrivilegesController extends AppController {
	protected function Initialize() {
		require_once(MODEL . "master/user_admin.php");
		require_once(MODEL . "master/user_privileges.php");
	}

	public function edit($uid = null) {
        $userPrivileges = null;
		$loader = null;
		// find user data
        //$log = new UserAdmin();
		$userdata = new UserAdmin();
		$userdata = $userdata->FindById($uid);
        $jdt = 0;
        $issetup = false;
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$rsId = $this->GetPostValue("resourceId");
            $mdIs1 = $this->GetPostValue("mDl1");
            $mdIs2 = $this->GetPostValue("mDl2");
            $mdIs3 = $this->GetPostValue("mDl3");
            $mdIs4 = $this->GetPostValue("mDl4");
            $mdIs5 = $this->GetPostValue("mDl5");
            $jdt = count($rsId);
            if ($jdt > 0){
                $userPrivileges = new UserPrivileges();
                $rs = $userPrivileges->Delete($uid);
                for($i=0;$i<$jdt;$i++){
                    $userPrivileges = new UserPrivileges();
                    $userPrivileges->UserUid = $uid;
                    $userPrivileges->ResourceId = $rsId[$i];
                    $userPrivileges->Mdl1 = $mdIs1[$i];
                    $userPrivileges->Mdl2 = $mdIs2[$i];
                    $userPrivileges->Mdl3 = $mdIs3[$i];
                    $userPrivileges->Mdl4 = $mdIs4[$i];
                    $userPrivileges->Mdl5 = $mdIs5[$i];
                    $rs = $userPrivileges->Insert();
                }
                //$log = $log->UserActivityWriter($this->userCabangId,'master.userprivileges','Update System User Privileges -> User: '.$userdata->UserId.' - '.$userdata->UserName,'-','Success');
                $this->persistence->SaveState("info", sprintf("Data Privileges User: '%s' telah berhasil disimpan.", $userdata->UserId));
                redirect_url("master.useradmin");
            }
		} else {
			$userPrivileges = new UserPrivileges();
            $userPrivileges = $userPrivileges->LoadPrivileges($uid);
            if ($userPrivileges != null){
                $issetup = true;
            }
		}
		// load resource data
		$loader = new UserPrivileges();
		$resources = $loader->LoadPrivilegesResource();
        $this->Set("userdata", $userdata);
        $this->Set("resources", $resources);
        $this->Set("issetup", $issetup);
		$this->Set("privileges", $userPrivileges);
	}

    public function getDiscPrivileges($resId){
        $userId = AclManager::GetInstance()->GetCurrentUser()->Id;
        $rst = -1;
        $privileges = new UserPrivileges();
        $privileges = $privileges->FindByResourceId($userId,$resId);
        if ($privileges != null){
            /** @var $privileges UserPrivileges */
            $rst = $privileges->Mdl1.'|'.$privileges->Mdl2.'|'.$privileges->Mdl3.'|'.$privileges->Mdl4.'|'.$privileges->Mdl5;
        }
        print $rst;
    }

    public function getLevelDiscPrivileges($resId,$level = 0){
        $userId = AclManager::GetInstance()->GetCurrentUser()->Id;
        $rst = -1;
        $privileges = new UserPrivileges();
        $privileges = $privileges->FindByResourceId($userId,$resId);
        if ($privileges != null){
            /** @var $privileges UserPrivileges */
            switch ($level){
                case 1:
                    $rst = $rst = $privileges->Mdl1;
                    break;
                case 2:
                    $rst = $rst = $privileges->Mdl2;
                    break;
                case 3:
                    $rst = $rst = $privileges->Mdl3;
                    break;
                case 4:
                    $rst = $rst = $privileges->Mdl4;
                    break;
                case 5:
                    $rst = $rst = $privileges->Mdl5;
                    break;
                default:
                    $rst = $privileges->Mdl1;
            }
        }
        print $rst;
    }
}
