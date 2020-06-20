<?php
class ItemEntityController extends AppController {
	private $userUid;
    private $userCabangId;
    private $userCompanyId;

	protected function Initialize() {
		require_once(MODEL . "inventory/itementity.php");
        require_once(MODEL . "master/user_admin.php");
        $this->userCabangId = $this->persistence->LoadState("cabang_id");
        $this->userCompanyId = $this->persistence->LoadState("company_id");
		$this->userUid = AclManager::GetInstance()->GetCurrentUser()->Id;
	}

	public function index() {
		$router = Router::GetInstance();
		$settings = array();

		$settings["columns"][] = array("name" => "a.id", "display" => "ID", "width" => 0);
		$settings["columns"][] = array("name" => "a.entity_code", "display" => "Kode", "width" => 50);
		$settings["columns"][] = array("name" => "a.entity_name", "display" => "Entitas", "width" => 100);
        $settings["columns"][] = array("name" => "concat(c.kode,' - ',c.perkiraan)", "display" => "Persediaan", "width" => 100);
        $settings["columns"][] = array("name" => "concat(f.kode,' - ',f.perkiraan)", "display" => "H P P", "width" => 100);
        $settings["columns"][] = array("name" => "concat(g.kode,' - ',g.perkiraan)", "display" => "Piutang", "width" => 100);
        $settings["columns"][] = array("name" => "concat(b.kode,' - ',b.perkiraan)", "display" => "Pendapatan", "width" => 100);
        $settings["columns"][] = array("name" => "concat(d.kode,' - ',d.perkiraan)", "display" => "Disc Jual", "width" => 100);
        $settings["columns"][] = array("name" => "concat(e.kode,' - ',e.perkiraan)", "display" => "Retur Jual", "width" => 100);
        $settings["columns"][] = array("name" => "concat(i.kode,' - ',i.perkiraan)", "display" => "Hutang", "width" => 100);
        $settings["columns"][] = array("name" => "concat(h.kode,' - ',h.perkiraan)", "display" => "Disc Beli", "width" => 100);
        $settings["columns"][] = array("name" => "concat(j.kode,' - ',j.perkiraan)", "display" => "Retur Beli", "width" => 100);

		$settings["filters"][] = array("name" => "a.entity_code", "display" => "Jenis Barang");
		$settings["filters"][] = array("name" => "a.entity_name", "display" => "Jenis");

		if (!$router->IsAjaxRequest) {
			$acl = AclManager::GetInstance();
			$settings["title"] = "Entitas Barang";

			if ($acl->CheckUserAccess("inventory.itementity", "add")) {
				$settings["actions"][] = array("Text" => "Add", "Url" => "inventory.itementity/add", "Class" => "bt_add", "ReqId" => 0);
			}
			if ($acl->CheckUserAccess("inventory.itementity", "edit")) {
				$settings["actions"][] = array("Text" => "Edit", "Url" => "inventory.itementity/edit/%s", "Class" => "bt_edit", "ReqId" => 1,
					"Error" => "Mohon memilih itementity terlebih dahulu sebelum proses edit.\nPERHATIAN: Mohon memilih tepat satu itementity.",
					"Confirm" => "");
			}
			if ($acl->CheckUserAccess("inventory.itementity", "delete")) {
				$settings["actions"][] = array("Text" => "Delete", "Url" => "inventory.itementity/delete/%s", "Class" => "bt_delete", "ReqId" => 1,
					"Error" => "Mohon memilih itementity terlebih dahulu sebelum proses penghapusan.\nPERHATIAN: Mohon memilih tepat satu itementity.",
					"Confirm" => "Apakah anda mau menghapus data itementity yang dipilih ?\nKlik OK untuk melanjutkan prosedur");
			}

			$settings["def_order"] = 1;
			$settings["def_filter"] = 0;
			$settings["singleSelect"] = true;

		} else {
		    $sql = "m_item_entity AS a";
		    $sql.= " Left Join m_account b On a.rev_acc_id = b.id";
            $sql.= " Left Join m_account c On a.ivt_acc_id = c.id";
            $sql.= " Left Join m_account d On a.sls_disc_acc_id = d.id";
            $sql.= " Left Join m_account e On a.ret_sls_acc_id = e.id";
            $sql.= " Left Join m_account f On a.hpp_acc_id = f.id";
            $sql.= " Left Join m_account g On a.ar_acc_id = g.id";
            $sql.= " Left Join m_account h On a.prc_disc_acc_id = h.id";
            $sql.= " Left Join m_account i On a.ap_acc_id = i.id";
            $sql.= " Left Join m_account j On a.ret_prc_acc_id = j.id";
			$settings["from"] = $sql;
            $settings["where"] = " a.company_id = ".$this->userCompanyId." And a.is_deleted = 0";
		}

		$dispatcher = Dispatcher::CreateInstance();
		$dispatcher->Dispatch("utilities", "flexigrid", array(), $settings, null, true);
	}

	private function ValidateData(ItemEntity $itementity) {

		return true;
	}

	public function add() {
        require_once(MODEL . "master/coadetail.php");
        $itementity = new ItemEntity();
        $log = new UserAdmin();
        if (count($this->postData) > 0) {
            $itementity->CompanyId = $this->userCompanyId;
            $itementity->EntityCode = $this->GetPostValue("EntityCode");
            $itementity->EntityName = $this->GetPostValue("EntityName");
            $itementity->IvtAccId = $this->GetPostValue("IvtAccId");
            $itementity->RevAccId = $this->GetPostValue("RevAccId");
            $itementity->RetSlsAccId = $this->GetPostValue("RetSlsAccId");
            $itementity->RetPrcAccId = $this->GetPostValue("RetPrcAccId");
            $itementity->ArAccId = $this->GetPostValue("ArAccId");
            $itementity->ApAccId = $this->GetPostValue("ApAccId");
            $itementity->HppAccId = $this->GetPostValue("HppAccId");
            $itementity->SlsDiscAccId = $this->GetPostValue("SlsDiscAccId");
            $itementity->PrcDiscAccId = $this->GetPostValue("PrcDiscAccId");
            //$itementity->ChartColor = $this->GetPostValue("ChartColor");
            if ($this->ValidateData($itementity)) {
                $itementity->CreatebyId = $this->userUid;
                $rs = $itementity->Insert();
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itementity','Add New Item Jenis -> Jenis: '.$itementity->EntityCode.' - '.$itementity->EntityName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Data Jenis: %s (%s) sudah berhasil disimpan", $itementity->EntityName, $itementity->EntityCode));
                    redirect_url("inventory.itementity");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itementity','Add New Item Jenis -> Jenis: '.$itementity->EntityCode.' - '.$itementity->EntityName,'-','Failed');
                    $this->Set("error", "Gagal pada saat menyimpan data.. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }
        $loader = new CoaDetail();
        $ivtCoa = $loader->LoadAll($this->userCompanyId);
        $this->Set("ivtcoa", $ivtCoa);
        $this->Set("itementity", $itementity);
	}

	public function edit($id = null) {
        require_once(MODEL . "master/coadetail.php");
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses edit.");
            redirect_url("inventory.itementity");
        }
        $log = new UserAdmin();
        $itementity = new ItemEntity();
        if (count($this->postData) > 0) {
            $itementity->Id = $id;
            $itementity->CompanyId = $this->userCompanyId;
            $itementity->EntityCode = $this->GetPostValue("EntityCode");
            $itementity->EntityName = $this->GetPostValue("EntityName");
            $itementity->IvtAccId = $this->GetPostValue("IvtAccId");
            $itementity->RevAccId = $this->GetPostValue("RevAccId");
            $itementity->RetSlsAccId = $this->GetPostValue("RetSlsAccId");
            $itementity->RetPrcAccId = $this->GetPostValue("RetPrcAccId");
            $itementity->ArAccId = $this->GetPostValue("ArAccId");
            $itementity->ApAccId = $this->GetPostValue("ApAccId");
            $itementity->HppAccId = $this->GetPostValue("HppAccId");
            $itementity->SlsDiscAccId = $this->GetPostValue("SlsDiscAccId");
            $itementity->PrcDiscAccId = $this->GetPostValue("PrcDiscAccId");
            //$itementity->ChartColor = $this->GetPostValue("ChartColor");
            if ($this->ValidateData($itementity)) {
                $itementity->UpdatebyId = $this->userUid;
                $rs = $itementity->Update($id);
                if ($rs == 1) {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itementity','Update Item Jenis -> Jenis: '.$itementity->EntityCode.' - '.$itementity->EntityName,'-','Success');
                    $this->persistence->SaveState("info", sprintf("Perubahan data satuan: %s (%s) sudah berhasil disimpan", $itementity->EntityName, $itementity->EntityCode));
                    redirect_url("inventory.itementity");
                } else {
                    $log = $log->UserActivityWriter($this->userCabangId,'inventory.itementity','Update Item Jenis -> Jenis: '.$itementity->EntityCode.' - '.$itementity->EntityName,'-','Failed');
                    $this->Set("error", "Gagal pada saat merubah data satuan. Message: " . $this->connector->GetErrorMessage());
                }
            }
        }else{
            $itementity = $itementity->LoadById($id);
            if ($itementity == null) {
                $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
                redirect_url("inventory.itementity");
            }
        }
        $loader = new CoaDetail();
        $ivtCoa = $loader->LoadAll($this->userCompanyId);
        $this->Set("ivtcoa", $ivtCoa);
        $this->Set("itementity", $itementity);
	}

	public function delete($id = null) {
        if ($id == null) {
            $this->persistence->SaveState("error", "Harap memilih data terlebih dahulu sebelum melakukan proses penghapusan data.");
            redirect_url("inventory.itementity");
        }
        $log = new UserAdmin();
        $itementity = new ItemEntity();
        $itementity = $itementity->LoadById($id);
        if ($itementity == null) {
            $this->persistence->SaveState("error", "Maaf data yang diminta tidak dapat ditemukan atau sudah dihapus.");
            redirect_url("inventory.itementity");
        }
        $rs = $itementity->Void($id);
        if ($rs == 1) {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.itementity','Delete Item Jenis -> Jenis: '.$itementity->EntityCode.' - '.$itementity->EntityName,'-','Success');
            $this->persistence->SaveState("info", sprintf("Jenis Barang: %s (%s) sudah dihapus", $itementity->EntityName, $itementity->EntityCode));
        } else {
            $log = $log->UserActivityWriter($this->userCabangId,'inventory.itementity','Delete Item Jenis -> Jenis: '.$itementity->EntityCode.' - '.$itementity->EntityName,'-','Failed');
            $this->persistence->SaveState("error", sprintf("Gagal menghapus jenis barang: %s (%s). Error: %s", $itementity->EntityName, $itementity->EntityCode, $this->connector->GetErrorMessage()));
        }
		redirect_url("inventory.itementity");
	}
}

// End of file: itementity_controller.php
