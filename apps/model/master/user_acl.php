<?php

class UserAcl extends EntityBase {
    // public variables
    public $ResourceId;
    public $MenuName;
    public $MenuSeq;
    public $ResourceName;
    public $ResourceSeq;
    public $ResourcePath;

    public $RightsId;
    public $UserUid;
    public $Rights;
    public $CabangId;
    public $CabangCode;

	// Helper Variable
	public function FillMenuProperties(array $row){
        $this->ResourceId = $row["id"];
        $this->MenuName = $row["menu_name"];
        $this->MenuSeq = $row["menu_seq"];
        $this->ResourceName = $row["resource_name"];
        $this->ResourceSeq = $row["resource_seq"];
        $this->ResourcePath = $row["resource_path"];
    }

    public function FillDetailProperties(array $row){
		$this->RightsId = $row["id"];
        $this->UserUid = $row["user_uid"];
        $this->ResourceId = $row["resource_id"];
        $this->Rights = $row["rights"];
        $this->CabangId = $row["cabang_id"];

		// Helper...
		$this->MenuName = $row["menu_name"];
		$this->MenuSeq = $row["menu_seq"];
		$this->ResourceName = $row["resource_name"];
		$this->ResourceSeq = $row["resource_seq"];
		$this->ResourcePath = $row["resource_path"];
    }


    public function LoadAllResources(){
        $this->connector->CommandText = "SELECT a.* FROM sys_resource AS a ORDER BY a.menu_seq, a.resource_seq";
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new UserAcl();
				$temp->FillMenuProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
    }

    public function LoadAcl($uid,$cbi){
        $this->connector->CommandText =
"SELECT a.*, b.menu_name, b.menu_seq, b.resource_name, b.resource_seq, b.resource_path
FROM sys_user_rights AS a
	JOIN sys_resource AS b ON a.resource_id = b.id
WHERE a.user_uid = ?uid and a.cabang_id = ?cbi
ORDER BY a.resource_id";
        $this->connector->AddParameter("?uid", $uid);
        $this->connector->AddParameter("?cbi", $cbi);
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new UserAcl();
				$temp->FillDetailProperties($row);
				$result[$temp->ResourceId] = $temp;
			}
		}
		return $result;
    }

    public function LoadAclByController($uid,$cbi,$ctl){
        $this->connector->CommandText =
            "SELECT a.*, b.menu_name, b.menu_seq, b.resource_name, b.resource_seq, b.resource_path
FROM sys_user_rights AS a
	JOIN sys_resource AS b ON a.resource_id = b.id
WHERE a.user_uid = ?uid and a.cabang_id = ?cbi and b.resource_path = ?ctl
ORDER BY a.resource_id";
        $this->connector->AddParameter("?uid", $uid);
        $this->connector->AddParameter("?cbi", $cbi);
        $this->connector->AddParameter("?ctl", $ctl);
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $row = $rs->FetchAssoc();
        $this->FillDetailProperties($row);
        return $this;
    }

    public function LoadAclResource($uid,$rsp){
        $this->connector->CommandText =
            "SELECT a.*, b.menu_name, b.menu_seq, b.resource_name, b.resource_seq, b.resource_path
            FROM sys_user_rights AS a
                JOIN sys_resource AS b ON a.resource_id = b.id
            WHERE a.user_uid = ?uid And b.resource_path = ?rsp
            ORDER BY a.resource_id";
        $this->connector->AddParameter("?uid", $uid);
        $this->connector->AddParameter("?rsp", $rsp);
        $rs = $this->connector->ExecuteQuery();
        $result = array();
        if ($rs != null) {
            while ($row = $rs->FetchAssoc()) {
                $temp = new UserAcl();
                $temp->FillDetailProperties($row);
                $result[$temp->ResourceId] = $temp;
            }
        }
        return $result;
    }

    public function Insert(){
        $this->connector->CommandText = 'INSERT INTO sys_user_rights(user_uid,resource_id,rights,cabang_id) VALUES(?user_uid,?resource_id,?rights,?cabang_id)';
		$this->connector->AddParameter("?user_uid", $this->UserUid);
        $this->connector->AddParameter("?resource_id", $this->ResourceId);
        $this->connector->AddParameter("?rights", $this->Rights);
        $this->connector->AddParameter("?cabang_id", $this->CabangId);
        return $this->connector->ExecuteNonQuery();
    }

    public function Copy($srcUid,$srcCabId, $dstUid,$dstCabId){

        $this->connector->CommandText = 'INSERT INTO sys_user_rights(user_uid,resource_id,rights,cabang_id) Select ?dstuid, a.resource_id,a.rights,?dstcabid From sys_user_rights as a Where a.user_uid = ?srcuid And a.cabang_id = ?srccabid';
        $this->connector->AddParameter("?dstuid", $dstUid);
        $this->connector->AddParameter("?srcuid", $srcUid);
        $this->connector->AddParameter("?dstcabid", $dstCabId);
        $this->connector->AddParameter("?srccabid", $srcCabId);
        return $this->connector->ExecuteNonQuery();
    }

    public function Delete($uid,$cbi = 0){
        $sql = "Delete From sys_user_rights WHERE user_uid = ?uid";
        //if ($cbi > 0){
            $sql.= " And cabang_id = ?cbi";
       // }
        $this->connector->CommandText = $sql;
		$this->connector->AddParameter("?uid", $uid);
        $this->connector->AddParameter("?cbi", $cbi);
		return $this->connector->ExecuteNonQuery();
    }

    public function LoadUserCabAcl($uid){
        $sql = "Select group_concat(a.cabang_id) as valresult from vw_user_cabang_acl_group a where a.user_uid = ".$uid;
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        $row = $rs->FetchAssoc();
        return $row["valresult"];
    }

    public function GetListUserCabAcl(){
        $sql = "Select * From vw_sys_user_cab_acl a Order By a.user_id, a.cabang_code";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }

    public function GetUserAclList($uid){
        $sql = "Select * From vw_sys_user_acl_lists a Where a.user_uid = $uid Order By a.user_id, a.cabang_code, a.menu_name, a.resource_name";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        return $rs;
    }
}

