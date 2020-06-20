<?php
class UseraclController extends AppController {
	protected function Initialize() {
		require_once(MODEL . "master/user_admin.php");
		require_once(MODEL . "master/user_acl.php");
	}

	public function add($uid = null) {
		$loader = null;
		$skema = null;
		$userlist = null;
		// find user data
		$userdata = new UserAdmin();
		$userlist = $userdata->LoadAll();
		$userdata = new UserAdmin();
		$userdata = $userdata->FindById($uid);
		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$acabang = $this->GetPostValue("aCabangId");
			$skema = $this->GetPostValue("hakakses");
			$prevResId = null;
			$hak = null;
			if ($acabang == null || $acabang == 0 || $acabang == ''){
				$userAcl = new UserAcl();
				$userAcl->Delete($uid,0);
				$this->persistence->SaveState("info", sprintf("Data Hak Akses User: '%s' telah berhasil dihapus..", $userdata->UserId));
				redirect_url("master.useradmin");
			}else {
				foreach ($acabang As $cabid) {
					$userAcl = new UserAcl();
					$userAcl->Delete($uid, $cabid);
					foreach ($skema As $aturan) {
						$tokens = explode("|", $aturan);
						$resid = $tokens[0];
						$hak = $tokens[1];
						if ($prevResId != $resid) {
							if ($userAcl->Rights != "") {
								$userAcl->Insert();
							}
							$prevResId = $resid;
							$userAcl = new UserAcl();
							$userAcl->ResourceId = $resid;
							$userAcl->UserUid = $uid;
							$userAcl->CabangId = $cabid;
							$userAcl->Rights = "";
						}
						$userAcl->Rights .= $hak;
					}
					if ($userAcl->Rights != "") {
						$userAcl->Insert();
					}
				}
				$this->persistence->SaveState("info", sprintf("Data Hak Akses User: '%s' telah berhasil disimpan.", $userdata->UserId));
				redirect_url("master.useradmin");
			}
		} else {
			$userAcl = new UserAcl();
			$hak = $userAcl->LoadAcl($uid);
            $loader = new UserAcl();
            $userCab = $loader->LoadUserCabAcl($uid);
		}
		// load resource data
		$loader = new UserAcl();
		$resources = $loader->LoadAllResources();
		$this->Set("resources", $resources);
		$this->Set("userdata", $userdata);
		$this->Set("userlist", $userlist);
		$this->Set("hak", $hak);
        $this->Set("userCab", $userCab);
	}

	public function view($uid = 0) {
		//load acl
		$userId = null;
		$userdata = new UserAdmin();
		$userdata = $userdata->FindById($uid);
		$userId = $userdata->UserId.' ['.$userdata->UserName.']';
		$userAcl = new UserAcl();
		$aclists = $userAcl->GetUserAclList($uid);
		$this->Set("userId", $userId);
		$this->Set("aclists", $aclists);
	}


	public function copy($uid = null) {

		$srcUid = null;

		if (count($this->postData) > 0) {
			// OK user ada kirim data kita proses
			$srcUid = $this->GetPostValue("copyFrom");
			$userAcl = new UserAcl();
			$userAcl->Delete($uid);
			$userAcl->Copy($srcUid, $uid);
			$this->persistence->SaveState("info", sprintf("Data Hak Akses telah berhasil disalin.."));
			Dispatcher::RedirectUrl("master.useracl/add/" . $uid);
		} else {
			$userAcl = new UserAcl();
			$hak = $userAcl->LoadAcl($uid);
			Dispatcher::RedirectUrl("master.useracl/add/" . $uid);
		}
	}



}
