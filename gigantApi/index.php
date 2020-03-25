<?php
include_once 'db.php';
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Header:*');
header('Access-Control-Allow-Methods:*');
header('Content-type:application/json');
header('Pragma: no-cache');
$db = new Conn('localhost','root','','gigant-live');
$request = explode('/',rtrim($_REQUEST['res'],'/'));
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if($request[0]=='user'){
            $db->selectAvailability($request[1]);
        }
        if(count($request)==1){
            $db->select('*')->from($request[0]);
        }
        if(count($request)==3){
            $db->select('*')->from($request[0])->where($request[1],$request[2]);
        }if($request[0]=='category'){
            $db->fetchcategory($request[1]);
        }
        if($request[0]=='product'){
            $db->fetchproduct($request[1]);
        }
        if($request[0]=='status'){
            $db->fetchstatus();
        }
        if($request[0]=='metavalue'){
            $db->select('meta_value')->from('wp_postmeta')->where($request[1],$request[2])->at('meta_key','_wc_appointment_pricing');
        }
        if($request[0]=='LoadList'){
            $db->select('fldUserID,template_name')->from('wp_savedListing')->where('fldUserID',$request[1])->groupby('template_name');
        }
        if($request[0]=='getTemplate'){
            $db->select('*')->from('wp_savedListing')->where('fldUserID',$request[1])->at('template_name',$request[2]);
        }
        
        
        $db->querys();
        break;
    case 'POST':
        if($request[0]=='availability'){
            $db->insert();
        }if($request[0]=='posts'){
            $db->insertPosts();
        }if($request[0]=='savedListing'){
            $db->savedListing('wp_savedListing');
        }if($request[0]=='savedTemplate'){
            $db->savedTemplate();
        }if($request[0]=='selectUpdate'){
            $db->selectUpdate($request[1]);
        }if($request[0]=='updateStatus'){
            $db->updateStatus($request[1]);
        }if($request[0]=='transacUpdateStatus'){
            $db->transacUpdateStatus($request[1]);
        }if($request[0]=='transacDeleteStatus'){
            $db->transacDeleteStatus($request[1]);
        }
        if($request[0]=='archiveStatus'){
            $db->archiveStatus($request[1]);
        }
        if($request[0]=='deleteStatus'){
            $db->deleteStatus($request[1]);
        }
        if($request[0]=='publishStatus'){
            $db->publishStatus($request[1]);
        }
        if($request[0]=='deleteTemplate'){
            $db->deleteTemplate($request[1],$request[2]);
        }
    break;
    
    case 'DELETE':
        if($request[0]=='trash'){
            $db->delete($request[1]);
        }
    break;

}



?>