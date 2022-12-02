<?php

declare(strict_types=1);
use GuzzleHttp\Client as HttpClient;
class UserLSItemDB{

    const TABLE_NAME = 'user_lso';
    protected $test_var= false;

    public function insert($user_items){
        global $DIC;
        $db = $DIC->database();
        $userID = $DIC->user()->getId();
        $ref_id = (int) $_GET['ref_id'];

        $obj = ilObjectFactory::getInstanceByRefId((int) 471);
        //tst_pass_result
        $pe=$obj->getTestParticipants();
        $pa = array($pe[7]);

        //$par = $obj->getTestParticipants();
       // foreach ($par as $user){
        //}

        if($this->checkExisting($ref_id, sizeof($user_items))){
            return;
        }
        foreach ($user_items as $item) {
            $values = array(
                "usr_id" => array("integer", $userID),
                "ref_id" => array("integer", $item->getRefId()),
                "odr_id" => array("integer", $item->getOrderNumber()),
                "lso_id" => array("integer", $ref_id)
            );
            $db->insert(static::TABLE_NAME, $values);
        }
    }


    public function checkExisting($ref_id, $count){
        global $DIC;
        $db = $DIC->database();
        $userID = $DIC->user()->getId();
        $result = $db->queryF("SELECT * FROM user_lso WHERE ".
            "usr_id = %s AND lso_id = %s",
            array("integer", "integer"),
            array($userID, $ref_id));

        $record = $db->numRows($result);
        if($record = $count){
            return true;
        }
        else{
            false;
        }

    }

    public function checkFailure(LSItem $lsitem){
        global $DIC;
        $db = $DIC->database();
        $settings = new ilSetting('Lp2Lrs');
        $lrsTypeId = $settings->get('lrs_type_id', 0);
        $lrsType = $lrsTypeId ? new ilCmiXapiLrsType($lrsTypeId) : null;

        // Distinction between ILIAS version 6 and 7 is made
        $nameMode = isset(array_flip(get_class_methods($lrsType))['getPrivacyName']) ? $lrsType->getPrivacyIdent() : $lrsType->getUserIdent();
        $userID = ilCmiXapiUser::getIdent($nameMode, $DIC->user());
        $userID2 = $DIC->user()->getId();
        $ref_id = (int) $_GET['ref_id'];
        if($ref_id != 0) {
        if($lsitem->getType()=="tst") {
            if ($this->test_var == false) {
                $obj = ilObjectFactory::getInstanceByRefId((int)$lsitem->getRefId());
                $par = $obj->getTestParticipants();
                foreach ($par as $user) {
                    if ($user['usr_id'] == $userID2) {
                        $userKey = array_search($user, $par);
                        $userArray = array(
                            $userKey => $user
                        );
                        $result = $obj->getAllTestResults($userArray);
                        foreach ($result as $re) {
                            if ($re['mark'] == '"passed"') {
                                $values = array(
                                    "ref_id" => $ref_id,
                                    "user_id" => $userID,
                                    "obj_id" => $lsitem->getRefId(),
                                    "check" => "above"
                                );
                                $client = new HttpClient([
                                    'base_uri' => 'https://verdatas.tkubica.com',
                                    'headers' => ['Content-Type' => 'application/json']
                                ]);
                                $test = array("hallo");
                                $response = $client->post('/api/v1/courses/LP/userLsoUpdate', [
                                    GuzzleHttp\RequestOptions::JSON => $values
                                ]);
                            } elseif ($re['mark'] == '"failed"') {
                                $values = array(
                                    "ref_id" => $ref_id,
                                    "user_id" => $userID,
                                    "obj_id" => $lsitem->getRefId(),
                                    "check" => "below"
                                );
                                $client = new HttpClient([
                                    'base_uri' => 'https://verdatas.tkubica.com',
                                    'headers' => ['Content-Type' => 'application/json']
                                ]);
                                $test = array("hallo");
                                $response = $client->post('/api/v1/courses/LP/userLsoUpdate', [
                                    GuzzleHttp\RequestOptions::JSON => $values
                                ]);
                            }
                        }
                    }
                }


            }
        }
        }




    }


    public function getLso(){
        global $DIC;
        $db = $DIC->database();
        $settings = new ilSetting('Lp2Lrs');
        $lrsTypeId = $settings->get('lrs_type_id', 0);
        $lrsType = $lrsTypeId ? new ilCmiXapiLrsType($lrsTypeId) : null;

        // Distinction between ILIAS version 6 and 7 is made
        $nameMode = isset(array_flip(get_class_methods($lrsType))['getPrivacyName']) ? $lrsType->getPrivacyIdent() : $lrsType->getUserIdent();
        $userID = ilCmiXapiUser::getIdent($nameMode, $DIC->user());
        $ref_id = (int) $_GET['ref_id'];
        if($ref_id != 0) {
            $values = array(
                "ref_id" => (int) $ref_id,
                "user_id" => $userID,
            );
            $client = new HttpClient([
                'base_uri' => 'https://verdatas.tkubica.com',
                'headers' => ['Content-Type' => 'application/json']
            ]);
            $response = $client->post('/api/v1/courses/LP/userLso', [
                GuzzleHttp\RequestOptions::JSON => $values
            ]);

            $result = $response->getBody();
            $result->seek(0);
            $read = $result->read(1024);
            $obj = json_decode($read);
            $resultIds =[];
            foreach ((array) $obj as $item){
                $resultIds[] = $item->{'ref_id'};
            }

            return $resultIds;



        }




    }







}