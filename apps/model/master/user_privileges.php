<?php

class UserPrivileges extends EntityBase {
    // public variables
    public $Id;
    public $UserUid;
    public $ResourceId;
    public $ResourceName;
    public $ResourcePath;
    public $Mdl1 = 0;
    public $Mdl2 = 0;
    public $Mdl3 = 0;
    public $Mdl4 = 0;
    public $Mdl5 = 0;


    // Helper Variable
	public function FillProperties(array $row){
        $this->Id = $row["id"];
        $this->UserUid = $row["user_uid"];
        $this->Mdl1 = $row["mdl1"];
        $this->Mdl2 = $row["mdl2"];
        $this->Mdl3 = $row["mdl3"];
        $this->Mdl4 = $row["mdl4"];
        $this->Mdl5 = $row["mdl5"];
        $this->ResourceId = $row["resource_id"];
        $this->ResourceName = $row["resource_name"];
        $this->ResourcePath = $row["resource_path"];
    }

    public function LoadPrivileges($uId){
        $sql = "SELECT a.*,b.resource_name,b.resource_path FROM sys_user_privileges AS a Join sys_resource AS b On a.resource_id = b.id Where a.user_uid = ".$uId." ORDER BY b.resource_name";
        $this->connector->CommandText = $sql;
		$rs = $this->connector->ExecuteQuery();
		$result = array();
		if ($rs != null) {
			while ($row = $rs->FetchAssoc()) {
				$temp = new UserPrivileges();
				$temp->FillProperties($row);
				$result[] = $temp;
			}
		}
		return $result;
    }

    public function FindByResourceId($uId,$rsId) {
        $sql = "SELECT a.*,b.resource_name,b.resource_path FROM sys_user_privileges AS a Join sys_resource AS b On a.resource_id = b.id Where a.user_uid = ".$uId." And a.resource_id = ".$rsId;
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return null;
        }
        $row = $rs->FetchAssoc();
        $this->FillProperties($row);
        return $this;
    }

    public function Insert(){
        $this->connector->CommandText = 'INSERT INTO sys_user_privileges(user_uid,resource_id,mdl1,mdl2,mdl3,mdl4,mdl5) VALUES(?user_uid,?resource_id,?mdl1,?mdl2,?mdl3,?mdl4,?mdl5)';
		$this->connector->AddParameter("?user_uid", $this->UserUid);
        $this->connector->AddParameter("?resource_id", $this->ResourceId);
        $this->connector->AddParameter("?mdl1", $this->Mdl1);
        $this->connector->AddParameter("?mdl2", $this->Mdl2);
        $this->connector->AddParameter("?mdl3", $this->Mdl3);
        $this->connector->AddParameter("?mdl4", $this->Mdl4);
        $this->connector->AddParameter("?mdl5", $this->Mdl5);
        return $this->connector->ExecuteNonQuery();
    }

    public function Delete($uid){
        $this->connector->CommandText = 'Delete From sys_user_privileges WHERE user_uid = ?uid';
		$this->connector->AddParameter("?uid", $uid);
		return $this->connector->ExecuteNonQuery();
    }

    public function LoadPrivilegesResource(){
        $sql = "SELECT a.id,a.resource_name,a.resource_path FROM sys_resource AS a Where a.is_privileges = 1 ORDER BY a.resource_name";
        $this->connector->CommandText = $sql;
        return $this->connector->ExecuteQuery();
    }

    public function LoadDiscPrivileges($uId,$rsId) {
        $sql = "SELECT a.* FROM sys_user_privileges AS a Where a.user_uid = $uId And a.resource_id = $rsId";
        $this->connector->CommandText = $sql;
        $rs = $this->connector->ExecuteQuery();
        if ($rs == null || $rs->GetNumRows() == 0) {
            return '0|0|0|0|0';
        }
        $row = $rs->FetchAssoc();
        //$this->FillProperties($row);
        return $row["mdl1"].'|'.$row['mdl2'].'|'.$row['mdl3'].'|'.$row['mdl4'].'|'.$row['mdl5'];
    }

}

