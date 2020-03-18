<?php
class Conn{
    protected $string;
    protected $db;
    function __construct($host,$username,$pass,$db){
        $this->db = mysqli_connect($host,$username,$pass,$db);
        if(mysqli_connect_error()){
            $info = ['status'=>200,'message'=>'Error Connection'];
            echo json_encode($info);
        }
        
    }
    public function select($fldname){
        $this->string = "SELECT $fldname ";
        return $this;
    }
    public function from($tblname){
        $this->string.="FROM $tblname ";
        return $this;
    }
    public function from2($tbl1,$tbl2){
        $this->string.="FROM $tbl1,$tbl2 ";
        return $this;
    }
    public function where($fldname,$id){
        $this->string .="WHERE $fldname = '$id' ";
        return $this;
    }
    public function groupby($fldname){
        $this->string .="GROUP BY $fldname";
        return $this;
    }
    public function limit($val){
        $this->string .= " LIMIT $val";
        return $this;
    }
    public function offsets($limit,$offset){
        $this->string .= " LIMIT $limit OFFSET $offset";
        return $this;
    }
    public function orderby($fld, $order='ASC'){
        $this->string .=" ORDER BY $fld $order LIMIT 1";
        
    }
    public function between($fld,$start,$end){
        $this->string .=" WHERE $fld BETWEEN '$start' AND '$end'";
        return $this;
    }
    public function like($fldname, $value){
        if($this->x == 0){
            $this->sql .= " WHERE $fldname LIKE '%$value%'";
        } else {
            $this->sql .= " AND $fldname LIKE '%$value%'";
        }
        return $this;
    }
    public function at($fld,$id){
        $this->string .="AND $fld = '$id' ";
        return $this;
    }
    public function selectAvailability($id){
        if(isset($id) && $id===1){
            $this->string = "SELECT wp_wc_appointments_availability.ID,wp_posts.post_title,wp_wc_appointments_availability.from_date,wp_wc_appointments_availability.to_date,wp_wc_appointments_availability.from_range,wp_wc_appointments_availability.to_range,wp_wc_appointments_availability.priority,wp_users.ID as userID FROM `wp_wc_appointments_availability`,wp_posts,wp_users where wp_wc_appointments_availability.kind_id = wp_posts.ID and wp_posts.post_author = wp_users.ID ORDER BY wp_posts.ID DESC";
        }else{
        $this->string = "SELECT wp_wc_appointments_availability.ID,wp_posts.post_title,wp_wc_appointments_availability.from_date,wp_wc_appointments_availability.to_date,wp_wc_appointments_availability.from_range,wp_wc_appointments_availability.to_range,wp_wc_appointments_availability.priority,wp_users.ID as userID FROM `wp_wc_appointments_availability`,wp_posts,wp_users where wp_wc_appointments_availability.kind_id = wp_posts.ID and wp_posts.post_author = wp_users.ID and wp_users.ID = $id ORDER BY wp_posts.ID DESC";
        }
        return $this;
    }
    public function querys(){
        $info = [];
        if($result= $this->db->query($this->string)){
            while($rows=$result->fetch_assoc()){
                array_push($info,$rows);
                
            }
        }else{
            
            $info = ['status'=>404,'message'=>'Data Not Found'];
        }
        echo json_encode($info);
    }
    
    public function insert(){
        //insert to table availability
        $data = json_decode(file_get_contents("php://input"));
//      print_r($data);
        $alldata = "";
        $arrayData=[];
        $count = 0;
        foreach($data as $key => $value){
            if($key != 0){
                $alldata .= " , ";
            }
            $alldata .= "(NULL";  
            foreach($data[$key] as $skey => $svalue){
                $alldata .= ", '" . $svalue . "'";
            }
            $alldata .= ",'')";
        }
        
        
        $new = explode(' , ',$alldata);
        foreach($new as $key){
            $this->string = "INSERT INTO wp_wc_appointments_availability VALUES $key";
            if($this->db->query($this->string)){
                $info = ['status'=>'success','message'=>'Data Created'];
                
            }else{
                $info = ['status'=>'error','message'=>'Data Not Created'];
            }
            echo json_encode($info);
        }
        
    }
    public function savedListing($tbl){
        $data = json_decode(file_get_contents("php://input"));
        $alldata="";
        foreach ($data as $key => $value) {
            $alldata .="(NULL";
            foreach($data[$key] as $skey => $svalue){
                $alldata .= ", '" .$svalue. "'";
            }
            $alldata .= ")";
        }
    
        $this->string = "INSERT INTO $tbl VALUES $alldata";
        if($this->db->query($this->string)){
            $info = ["status"=>201,"message"=>"Saved Appointment Details"];
            
        }else{
            $info = ["status"=>404,"message"=>"Data Not Saved"];
        }
        echo json_encode($info);
    }
     public function savedTemplate(){
         //insert to savedListing table
        $data = json_decode(file_get_contents("php://input"));
        // print_r($data);
        $alldata = "";
        $arrayData=[];
        $count = 0;
        $userid;
        foreach($data as $key => $value){
            foreach($data[$key] as $skey=>$svalue){
              if($skey=="currentUser"){
                $userid = $svalue;
            }
            }
        }
        foreach($data as $key => $value){
            if($key != 0){
                $alldata .= " , ";
            }
            $alldata .= "(NULL";  
            foreach($data[$key] as $skey => $svalue){
                if($skey !=0){
                    
                }
                $alldata .= ", '" . $svalue . "'";
            }
            $alldata .= ")";
        }
        
        $new = explode(' , ',$alldata);
    //  print_r($new);
        foreach($new as $key){
            // echo 'sample<br>';
        $this->string = "INSERT INTO wp_savedListing VALUES $key";
            if($this->db->query($this->string)){
                $info = ['status'=>'success','message'=>'Appointment Template Saved'];
            }else{
                $info = ['status'=>'error','message'=>'Data Not Saved'];
            }
            echo json_encode($info);
            }
        
    }
    public function insertPosts(){
        // insert to table posts
        $data = json_decode(file_get_contents("php://input"));
        // print_r($data);
        $alldata = "";
        $count = 0;
        foreach($data as $key => $value){
            if($key != 0){
                $alldata .= ", ";
            }
            $alldata .= "(NULL";  
            foreach($data[$key] as $skey => $svalue){
                $alldata .= ", '" . $svalue . "'";
            }
            $alldata .= ")";
            $count++;
        }
        
//      print_r($alldata);
        $this->string = "INSERT INTO wp_posts VALUES $alldata";

        $info=[];
        if($this->db->query($this->string)){
            $select = "SELECT LAST_INSERT_ID() as lastID ";
            $result = $this->db->query($select);
            while($rows=$result->fetch_assoc()){
                array_push($info,$rows);
            }
        }else{
            $info = ['status'=>404,'message'=>'Data Not Found'];
        }
        echo json_encode($info);

    }
    public function selectUpdate($prodID){
       $data = json_decode(file_get_contents("php://input"));
        $this->string = "SELECT meta_id FROM wp_postmeta WHERE post_id = $prodID AND meta_key='_wc_appointment_pricing'";
        $info=[];
        if($resultSelect=$this->db->query($this->string)){
            $meta_id=$resultSelect->fetch_assoc();
            foreach($meta_id as $key){
            $selectUpdate = "UPDATE wp_postmeta SET meta_value = '".$data->meta."' WHERE meta_id = $key ";
            if($this->db->query($selectUpdate)){
                $info = ['status'=>201,'message'=>'meta Updated'];
                echo json_encode($info);
            }
            }
        }
    }
    public function updateStatus($id){
        $info=[];
        $this->string = "UPDATE wp_posts SET post_status='trash' WHERE ID=$id";
        if($this->db->query($this->string)){
            $info=['status'=>200,'message'=>'Data Updated'];
            echo json_encode($info);
        }
    }
    public function transacUpdateStatus($id){
        $info=[];
        $this->string = "UPDATE wp_posts SET post_status='confirmed' WHERE ID=$id";
        if($this->db->query($this->string)){
            $info=['status'=>200,'message'=>'Data Updated'];
            echo json_encode($info);
        }
    }
    
    public function transacDeleteStatus($id){
        $info=[];
        $this->string = "UPDATE wp_posts SET post_status='cancelled' WHERE ID=$id";
        if($this->db->query($this->string)){
            $info=['status'=>200,'message'=>'Data Updated'];
            echo json_encode($info);
        }
    }
    
    
    public function archiveStatus($id){
        $info=[];
        $this->string = "UPDATE wp_posts SET post_status='archived' WHERE ID=$id";
        if($this->db->query($this->string)){
            $info=['status'=>200,'message'=>'Data Updated'];
            echo json_encode($info);
        }
    }
     public function deleteStatus($id){
        $info=[];
        $this->string = "UPDATE wp_posts SET post_status='trash' WHERE ID=$id";
        if($this->db->query($this->string)){
            $info=['status'=>200,'message'=>'Data Updated'];
            echo json_encode($info);
        }
    }
    public function publishStatus($id){
        $info=[];
        $this->string = "UPDATE wp_posts SET post_status='publish' WHERE ID=$id";
        if($this->db->query($this->string)){
            $info=['status'=>200,'message'=>'Data Updated'];
            echo json_encode($info);
        }
    }
    
    public function delete($id){
        $this->string = "DELETE FROM wp_wc_appointments_availability WHERE ID = $id";
        if($this->db->query($this->string)){
            $info = ['status'=>200, 'message'=>'Data Trash Success'];
            echo json_encode($info);
        }
    }
    public function deleteTemplate($name,$id){
        $data = json_decode(file_get_contents("php://input"));
        $this->string = "DELETE FROM wp_savedListing WHERE template_name = '$data->tempName' AND fldUserID = $data->userID ";
        if($this->db->query($this->string)){
            $info = ['status'=>200, 'message'=>'Data Trash Success'];
            echo json_encode($info);
        }
    }
    
    public function fetchcategory($name){
        $this->string = "SELECT u.ID,
      p.ID, p.post_status,
      p.post_title,
      t.name AS product_category,
      t.term_id AS product_id,
      t.slug AS product_slug
    FROM wp_posts AS p 
    LEFT JOIN wp_postmeta pm1 ON pm1.post_id = p.ID
    LEFT JOIN wp_term_relationships AS tr ON tr.object_id = p.ID
    JOIN wp_term_taxonomy AS tt ON tt.taxonomy = 'product_cat' AND tt.term_taxonomy_id = tr.term_taxonomy_id 
    JOIN wp_terms AS t ON t.term_id = tt.term_id
    JOIN wp_users AS u ON p.post_author=u.ID 
    WHERE  pm1.post_id = p.ID 
   AND p.post_author= u.ID 
   AND t.name = '$name'
    GROUP BY p.ID, p.post_title";
    $info=[];
    if($result=$this->db->query($this->string)){
        while($rows = $result->fetch_assoc()){
            array_push($info,$rows);
        }
 
        json_encode($info);
        }
    }
    public function fetchstatus(){
        $this->string = "SELECT u.display_name,u.ID,
      p.ID, p.post_title, post_status,
      t.name AS product_category,
      t.term_id AS product_id,
      t.slug AS product_slug
    FROM wp_posts AS p 
    LEFT JOIN wp_postmeta pm1 ON pm1.post_id = p.ID
    LEFT JOIN wp_term_relationships AS tr ON tr.object_id = p.ID
    JOIN wp_term_taxonomy AS tt ON tt.taxonomy = 'product_cat' AND tt.term_taxonomy_id = tr.term_taxonomy_id 
    JOIN wp_terms AS t ON t.term_id = tt.term_id
    JOIN wp_users AS u ON p.post_author=u.ID 
    WHERE p.post_type AND p.post_status='publish' OR p.post_status='draft' OR p.post_status='archived' OR p.post_status='pending' OR p.post_status='trash'  AND p.post_content <> ''
    GROUP BY product_category, post_title";
    $info=[];
    if($result=$this->db->query($this->string)){
        while($rows = $result->fetch_assoc()){
            array_push($info,$rows);
        }
 
        json_encode($info);
        }
    }
}



?>