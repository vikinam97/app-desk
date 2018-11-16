<?php

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 */

if(!defined('OPE_THEME_REQUIRED_PHP_VERSION')){
    define('OPE_THEME_REQUIRED_PHP_VERSION','5.3.0');
}

function ajaxurl() {

?>
<script type="text/javascript">
var ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';

</script>
<?php
}

add_action('wp_head','ajaxurl');


function theme_enqueue_scripts() {

	// Enqueue jQuery UI and autocomplete
	wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-autocomplete' );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_scripts' );

add_action( 'after_switch_theme', 'one_page_express_check_php_version' );

function one_page_express_check_php_version(){
  // Compare versions.
  if ( version_compare(phpversion(), OPE_THEME_REQUIRED_PHP_VERSION, '<') ) :
    // Theme not activated info message.
    add_action( 'admin_notices', 'one_page_express_php_version_notice' );
    

    // Switch back to previous theme.
    switch_theme(get_option( 'theme_switched' )  );
    return false;
  endif;
}

function one_page_express_php_version_notice() {
    ?>
    <div class="notice notice-alt notice-error notice-large">
        <h4><?php _e('One Page Express theme activation failed!','one-page-express'); ?></h4>
        <p>
            <?php _e( 'You need to update your PHP version to use the <strong>One Page Express</strong>.', 'one-page-express' ); ?> <br />
            <?php _e( 'Current php version is:', 'one-page-express' ) ?> <strong>
            <?php echo phpversion(); ?></strong>, <?php _e( 'and the minimum required version is ', 'one-page-express' ) ?> 
            <strong><?php echo OPE_THEME_REQUIRED_PHP_VERSION; ?></strong>
        </p>
    </div>
    <?php
}
function my_wp_nav_menu_args( $args = '' ) {
 
if( is_user_logged_in() ) { 
    $args['menu'] = 'Menu 2';
} else { 
    $args['menu'] = 'Menu 1';
} 
    return $args;
}
add_filter( 'wp_nav_menu_args', 'my_wp_nav_menu_args' );
if( version_compare(phpversion(), OPE_THEME_REQUIRED_PHP_VERSION, '>=')){
    require_once get_template_directory() . "/inc/functions.php";
}

    
////////////////////////////////////////////////////////////////////////
// MyCred User Ranks and Badges Integration ////////////////////////////
////////////////////////////////////////////////////////////////////////
add_filter('wpdiscuz_after_label', 'wpdiscuz_mc_after_label_html', 110, 2);
function wpdiscuz_mc_after_label_html($afterLabelHtml, $comment) {
    if ($comment->user_id) {
        if (function_exists('mycred_get_users_rank')) { //User Rank
            $afterLabelHtml .= mycred_get_users_rank($comment->user_id, 'logo', 'post-thumbnail', array('class' => 'mycred-rank'));
        }
        if (function_exists('mycred_get_users_badges')) { //User Badges
            $users_badges = mycred_get_users_badges($comment->user_id);
            if (!empty($users_badges)) {
                foreach ($users_badges as $badge_id => $level) {
                    $imageKey = ( $level > 0 ) ? 'level_image' . $level : 'main_image';
                    $afterLabelHtml .= '<img src="' . get_post_meta($badge_id, $imageKey, true) . '" width="22" height="22" class="mycred-badge earned" alt="' . get_the_title($badge_id) . '" title="' . get_the_title($badge_id) . '" />';
                }
            }
        }        
    }
    return $afterLabelHtml;
}



function select_query_json($sqlqry_select) { 
	error_reporting(0);
  $client = new SoapClient("http://172.16.0.166:8080/cdata.asmx?Wsdl"); 
  $get_parameter->Qry_String=$sqlqry_select;
  try{
    $get_result=$client->Get_Data_xml($get_parameter)->Get_Data_XMLResult;
  }
  catch(SoapFault $fault){
    echo "Fault code:{$fault->faultcode}".NEWLINE;
    echo "Fault string:{$fault->faultstring}".NEWLINE;
    if ($client != null)
    {
      $client=null;
    }
    // exit();
  }  
  $soapClient = null;
  return json_decode($get_result,true); 
}



// Select Query JSON Response
function select_query_json_test($sqlqry_select, $brn_connection = 'Centra', $schema_source = 'TEST') {
	// echo "**".$sqlqry_select."**".$brn_connection."**".$schema_source."**";
	$client = new SoapClient("http://www.templiveservice.com/service.asmx?Wsdl"); 
	$get_parameter->str = $sqlqry_select;
	$get_parameter->B_Code = $brn_connection; // 'Centra';
	$get_parameter->C_Mode = $schema_source; // 'TCS';
	// $get_parameter->C_Mode='TEST';
	try{
		//echo "!!";
		// print_r($get_parameter);
		$get_result=$client->GetData_Json($get_parameter)->GetData_JsonResult;
	}
	catch(SoapFault $fault){
		/* echo "##";
		echo "Fault code:{$fault->faultcode}".NEWLINE;
		echo "Fault string:{$fault->faultstring}".NEWLINE; */
		if ($client != null)
		{
			$client=null;
		}
		// exit();
	}
	$soapClient = null;
	return json_decode($get_result,true); 
}
// Select Query JSON Response



function checkUser() {  
if($_REQUEST['type'] == 'branch_employee'){
	$result = select_query_json("select distinct(emp.EMPSRNO), emp.EMPCODE, emp.EMPNAME, emp.ESECODE, sec.ESENAME, emp.DATEOFJOIN, des.DESNAME, sal.PAYCOMPANY from employee_office emp, empsection sec, designation des, employee_salary sal where emp.ESECODE = sec.ESECODE and emp.DESCODE = des.DESCODE and (emp.empcode not between 11 and 1000) and sec.deleted = 'N' and des.deleted = 'N' and sal.PAYCOMPANY = 1 and emp.empsrno = sal.empsrno and ( emp.EMPCODE like '%".strtoupper($_REQUEST['name_startsWith'])."%' or emp.EMPNAME like '%".strtoupper($_REQUEST['name_startsWith'])."%' ) and emp.brncode = '".$_REQUEST['branch']."' union	select distinct(emp.EMPSRNO), emp.EMPCODE, emp.EMPNAME, emp.ESECODE, sec.ESENAME, emp.DATEOFJOIN, des.DESNAME, sal.PAYCOMPANY from employee_office emp, new_empsection sec, new_designation des, employee_salary sal where emp.ESECODE = sec.ESECODE and emp.DESCODE = des.DESCODE and (emp.empcode not between 11 and 1000) and sec.deleted = 'N' and des.deleted = 'N' and sal.PAYCOMPANY = 2 and emp.empsrno = sal.empsrno and ( emp.EMPCODE like '%".strtoupper($_REQUEST['name_startsWith'])."%' or 
emp.EMPNAME like '%".strtoupper($_REQUEST['name_startsWith'])."%' ) and emp.brncode = '".$_REQUEST['branch']."' order by EMPCODE Asc", "Centra", 'TCS');
    $data = array();
	for($rowi = 0; $rowi < count($result);$rowi++) {
		array_push($data, $result[$rowi]['EMPCODE']." - ".$result[$rowi]['EMPNAME']." - ".substr($result[$rowi]['ESENAME'], 3));
    }    
echo json_encode($data);
   
}
	die();
}
add_action('wp_ajax_check_user', 'checkUser');
add_action('wp_ajax_nopriv_check_user', 'checkUser');



function checkphoto() { 
	$branch=$_REQUEST['branch'];
	$profile_img=$_REQUEST['profile_img'];
	if($_REQUEST['action2']=='userprofileimg')
{
	$result = select_query_json("select distinct(emp.EMPSRNO), emp.EMPCODE, emp.EMPNAME, emp.ESECODE, sec.ESENAME, emp.DATEOFJOIN, des.DESNAME, sal.PAYCOMPANY 
										from employee_office emp, empsection sec, designation des, employee_salary sal 
										where emp.ESECODE = sec.ESECODE and emp.DESCODE = des.DESCODE and (emp.empcode not between 11 and 1000) and sec.deleted = 'N' and des.deleted = 'N' and 
											sal.PAYCOMPANY = 1 and emp.empsrno = sal.empsrno and ( emp.EMPCODE like '%".$_REQUEST['profile_img']."%' or 
											emp.EMPNAME like '%".strtoupper($_REQUEST['empname'])."%' ) and emp.brncode = '".$branch."' 
									union
										select distinct(emp.EMPSRNO), emp.EMPCODE, emp.EMPNAME, emp.ESECODE, sec.ESENAME, emp.DATEOFJOIN, des.DESNAME, sal.PAYCOMPANY 
										from employee_office emp, new_empsection sec, new_designation des, employee_salary sal 
										where emp.ESECODE = sec.ESECODE and emp.DESCODE = des.DESCODE and (emp.empcode not between 11 and 1000) and sec.deleted = 'N' and des.deleted = 'N' and 
											sal.PAYCOMPANY = 2 and emp.empsrno = sal.empsrno and ( emp.EMPCODE like '%".$_REQUEST['profile_img']."%' or 
											emp.EMPNAME like '%".strtoupper($_REQUEST['empname'])."%' ) and emp.brncode = '".$branch."' 
										order by EMPCODE Asc", "Centra", 'TCS');
	if(count($result) > 0) {
		$sql_sub = select_query_json("select p.empphot from employee_personal p, employee_office o where p.empsrno=o.empsrno and o.empcode=".$profile_img, "Centra", "TCS");
		$data = base64_decode($sql_sub[0]['EMPPHOT']);
		$im = imagecreatefromstring($data);
		if ($im !== false) {
			echo $sql_sub[0]['EMPPHOT'];
			//echo imagepng($im);
			// imagedestroy($im);
		}
		else {
			echo 'An error occurred.';
		}
	} else {
		echo 'An error occurred.';
	}
}
	die();
}
add_action('wp_ajax_check_photo', 'checkphoto');
add_action('wp_ajax_nopriv_check_photo', 'checkphoto');

function checkreport() { 
	if($_REQUEST['action3']=='alerttext')
{       
		$usrnot = select_query_json_test("select count(*)+1 MAXNOT from employee_notice_detail WHERE EMPCODE='".$_REQUEST['empsrno']."'", "Centra", 'TEST');
	 echo ('ALERT NOTICE-'.$usrnot[0]['MAXNOT']);
 	
}
       
	    die();
}
add_action('wp_ajax_check_report', 'checkreport');
add_action('wp_ajax_nopriv_check_report', 'checkreport');

function printReport() {  
	$SIGNAME=array("KUMARAN K", "S KAARTHI", "PADHMA SIVLINGAM S", "SIVALINGAM K");
		$SIGN=array('452', '21344', '43400', '20118');
$sql_brn = select_query_json_test("Select end.*,c.ctyname,esec.eseNAME From employee_notice_detail end,empsection esec,city c,branch b 
									Where esec.esecode=end.esecode and c.ctycode=b.ctycode and b.brncode=end.brncode and notyear='".$_REQUEST['notyear']."' and notnumb='".$_REQUEST['notnumb']."'", "Centra", 'TEST');
	$addname = select_query_json("Select EMPNAME From employee_OFFICE Where empsrno='".$_REQUEST['authby']."'", "Centra", 'TCS');
	
	$authname = select_query_json("Select EMPNAME From employee_OFFICE Where EMPSRNO='".$sql_brn[0]['AUTSRNO']."'", "Centra", 'TCS');
	
	$folder_path = "approval_desk/digital_signature/";	
	echo "<div class='container'>
		<div class='row'>
			<div class='col-md-12'>
				<h1 style='text-align:center;'><u>NOTICE</u></h1>
				<table style='width:100%'>
					<tr>
					    <td style='text-align:right;' width='85%'>DATE : </td><td style='text-align:left;' width='15%'>".strtoupper(date('d-M-Y'))."</td></tr>
					  <tr>  <td style='text-align:right;' width='85%'>REF NO : </td> <td style='text-align:left;' width='15%'>".$sql_brn[0]['NOTYEAR']."-".$sql_brn[0]['NOTNUMB']."</td></tr>	
					  </tr>
				</table>
				
				<table style='width:100%'>
					  
					  <tr>
					    <td align='right' width='15%'>TO :</td>
					    <td align='left'></td> 					   
					  </tr>
					  <tr>
					    <td align='right' width='15%'></td>
					    <td align='left'>".$sql_brn[0]['EMPNAME']."</td> 					   
					  </tr>
					  <tr>
					    <td align='right' width='15%'></td>
					    <td align='left'>EC NO : ".$sql_brn[0]['EMPCODE']."</td> 					   
					  </tr>
					  <tr>
					    <td align='right' width='15%'></td>
					    <td align='left'>".$sql_brn[0]['ESENAME']."</td> 					   
					  </tr>
					  <tr>
					    <td align='right' width='15%'></td>
					    <td align='left'>THE CHENNAI SILKS - ".($sql_brn[0]['BRNCODE']=='888'?"CORPORATE OFFICE":"")."</td> 					   
					  </tr>
					  <tr>
					    <td align='right' width='15%'></td>
					    <td align='left'>TIRUPPUR</td> 					   
					  </tr>

					   <tr>
					    <td align='right' width='15%'>FROM :</td>
					    <td align='left'></td> 					   
					  </tr>
					  <tr>
					    <td align='right' width='15%'></td>
					    <td align='left'>".$authname[0]['EMPNAME']."</td> 					   
					  </tr>
					  <tr>
					    <td align='right' width='15%'></td>
					    <td align='left'>THE CHENNAI SILKS - ( CORPORATE OFFICE )</td> 					   
					  </tr>
					  <tr>
					    <td align='right' width='15%'></td>
					    <td align='left'>".$sql_brn[0]['CTYNAME']."</td> 					   
					  </tr>
					  <tr>
					    <td align='right' width='15%'>SUB :</td>
					    <td align='left'></td> 						     
					  </tr>
					  <tr class='subject'>
					  	<td align='right' width='15%'></td> 	
					  	<td align='left' width='85%'>".$sql_brn[0]['REMARKS']."</td>  
					  </tr>
					  
				</table>
				
			</div>
		</div>
		</div>";
	die();
}
add_action('wp_ajax_print_report', 'printReport');
add_action('wp_ajax_nopriv_print_report', 'printReport');




?>