<?php
require_once( dirname(__FILE__).'/form.lib.php' );

define( 'PHPFMG_USER', "changeme@change.com" ); // must be a email address.
define( 'PHPFMG_PW', "changeme" );

?>
<?php
/**
 * GNU Library or Lesser General Public License version 2.0 (LGPLv2)
*/

# main
# ------------------------------------------------------
error_reporting( E_ERROR ) ;
phpfmg_admin_main();
# ------------------------------------------------------




function phpfmg_admin_main(){
    $mod  = isset($_REQUEST['mod'])  ? $_REQUEST['mod']  : '';
    $func = isset($_REQUEST['func']) ? $_REQUEST['func'] : '';
    $function = "phpfmg_{$mod}_{$func}";
    if( !function_exists($function) ){
        phpfmg_admin_default();
        exit;
    };

    // no login required modules
    $public_modules   = false !== strpos('|captcha|', "|{$mod}|", "|ajax|");
    $public_functions = false !== strpos('|phpfmg_ajax_submit||phpfmg_mail_request_password||phpfmg_filman_download||phpfmg_image_processing||phpfmg_dd_lookup|', "|{$function}|") ;   
    if( $public_modules || $public_functions ) { 
        $function();
        exit;
    };
    
    return phpfmg_user_isLogin() ? $function() : phpfmg_admin_default();
}

function phpfmg_ajax_submit(){
    $phpfmg_send = phpfmg_sendmail( $GLOBALS['form_mail'] );
    $isHideForm  = isset($phpfmg_send['isHideForm']) ? $phpfmg_send['isHideForm'] : false;

    $response = array(
        'ok' => $isHideForm,
        'error_fields' => isset($phpfmg_send['error']) ? $phpfmg_send['error']['fields'] : '',
        'OneEntry' => isset($GLOBALS['OneEntry']) ? $GLOBALS['OneEntry'] : '',
    );
    
    @header("Content-Type:text/html; charset=$charset");
    echo "<html><body><script>
    var response = " . json_encode( $response ) . ";
    try{
        parent.fmgHandler.onResponse( response );
    }catch(E){};
    \n\n";
    echo "\n\n</script></body></html>";

}


function phpfmg_admin_default(){
    if( phpfmg_user_login() ){
        phpfmg_admin_panel();
    };
}



function phpfmg_admin_panel()
{    
    phpfmg_admin_header();
    phpfmg_writable_check();
?>    
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td valign=top style="padding-left:280px;">

<style type="text/css">
    .fmg_title{
        font-size: 16px;
        font-weight: bold;
        padding: 10px;
    }
    
    .fmg_sep{
        width:32px;
    }
    
    .fmg_text{
        line-height: 150%;
        vertical-align: top;
        padding-left:28px;
    }

</style>

<script type="text/javascript">
    function deleteAll(n){
        if( confirm("Are you sure you want to delete?" ) ){
            location.href = "admin.php?mod=log&func=delete&file=" + n ;
        };
        return false ;
    }
</script>


<div class="fmg_title">
    1. Email Traffics
</div>
<div class="fmg_text">
    <a href="admin.php?mod=log&func=view&file=1">view</a> &nbsp;&nbsp;
    <a href="admin.php?mod=log&func=download&file=1">download</a> &nbsp;&nbsp;
    <?php 
        if( file_exists(PHPFMG_EMAILS_LOGFILE) ){
            echo '<a href="#" onclick="return deleteAll(1);">delete all</a>';
        };
    ?>
</div>


<div class="fmg_title">
    2. Form Data
</div>
<div class="fmg_text">
    <a href="admin.php?mod=log&func=view&file=2">view</a> &nbsp;&nbsp;
    <a href="admin.php?mod=log&func=download&file=2">download</a> &nbsp;&nbsp;
    <?php 
        if( file_exists(PHPFMG_SAVE_FILE) ){
            echo '<a href="#" onclick="return deleteAll(2);">delete all</a>';
        };
    ?>
</div>

<div class="fmg_title">
    3. Form Generator
</div>
<div class="fmg_text">
    <a href="http://www.formmail-maker.com/generator.php" onclick="document.frmFormMail.submit(); return false;" title="<?php echo htmlspecialchars(PHPFMG_SUBJECT);?>">Edit Form</a> &nbsp;&nbsp;
    <a href="http://www.formmail-maker.com/generator.php" >New Form</a>
</div>
    <form name="frmFormMail" action='http://www.formmail-maker.com/generator.php' method='post' enctype='multipart/form-data'>
    <input type="hidden" name="uuid" value="<?php echo PHPFMG_ID; ?>">
    <input type="hidden" name="external_ini" value="<?php echo function_exists('phpfmg_formini') ?  phpfmg_formini() : ""; ?>">
    </form>

		</td>
	</tr>
</table>

<?php
    phpfmg_admin_footer();
}



function phpfmg_admin_header( $title = '' ){
    header( "Content-Type: text/html; charset=" . PHPFMG_CHARSET );
?>
<html>
<head>
    <title><?php echo '' == $title ? '' : $title . ' | ' ; ?>PHP FormMail Admin Panel </title>
    <meta name="keywords" content="PHP FormMail Generator, PHP HTML form, send html email with attachment, PHP web form,  Free Form, Form Builder, Form Creator, phpFormMailGen, Customized Web Forms, phpFormMailGenerator,formmail.php, formmail.pl, formMail Generator, ASP Formmail, ASP form, PHP Form, Generator, phpFormGen, phpFormGenerator, anti-spam, web hosting">
    <meta name="description" content="PHP formMail Generator - A tool to ceate ready-to-use web forms in a flash. Validating form with CAPTCHA security image, send html email with attachments, send auto response email copy, log email traffics, save and download form data in Excel. ">
    <meta name="generator" content="PHP Mail Form Generator, phpfmg.sourceforge.net">

    <style type='text/css'>
    body, td, label, div, span{
        font-family : Verdana, Arial, Helvetica, sans-serif;
        font-size : 12px;
    }
    </style>
</head>
<body  marginheight="0" marginwidth="0" leftmargin="0" topmargin="0">

<table cellspacing=0 cellpadding=0 border=0 width="100%">
    <td nowrap align=center style="background-color:#024e7b;padding:10px;font-size:18px;color:#ffffff;font-weight:bold;width:250px;" >
        Form Admin Panel
    </td>
    <td style="padding-left:30px;background-color:#86BC1B;width:100%;font-weight:bold;" >
        &nbsp;
<?php
    if( phpfmg_user_isLogin() ){
        echo '<a href="admin.php" style="color:#ffffff;">Main Menu</a> &nbsp;&nbsp;' ;
        echo '<a href="admin.php?mod=user&func=logout" style="color:#ffffff;">Logout</a>' ;
    }; 
?>
    </td>
</table>

<div style="padding-top:28px;">

<?php
    
}


function phpfmg_admin_footer(){
?>

</div>

<div style="color:#cccccc;text-decoration:none;padding:18px;font-weight:bold;">
	:: <a href="http://phpfmg.sourceforge.net" target="_blank" title="Free Mailform Maker: Create read-to-use Web Forms in a flash. Including validating form with CAPTCHA security image, send html email with attachments, send auto response email copy, log email traffics, save and download form data in Excel. " style="color:#cccccc;font-weight:bold;text-decoration:none;">PHP FormMail Generator</a> ::
</div>

</body>
</html>
<?php
}


function phpfmg_image_processing(){
    $img = new phpfmgImage();
    $img->out_processing_gif();
}


# phpfmg module : captcha
# ------------------------------------------------------
function phpfmg_captcha_get(){
    $img = new phpfmgImage();
    $img->out();
    //$_SESSION[PHPFMG_ID.'fmgCaptchCode'] = $img->text ;
    $_SESSION[ phpfmg_captcha_name() ] = $img->text ;
}



function phpfmg_captcha_generate_images(){
    for( $i = 0; $i < 50; $i ++ ){
        $file = "$i.png";
        $img = new phpfmgImage();
        $img->out($file);
        $data = base64_encode( file_get_contents($file) );
        echo "'{$img->text}' => '{$data}',\n" ;
        unlink( $file );
    };
}


function phpfmg_dd_lookup(){
    $paraOk = ( isset($_REQUEST['n']) && isset($_REQUEST['lookup']) && isset($_REQUEST['field_name']) );
    if( !$paraOk )
        return;
        
    $base64 = phpfmg_dependent_dropdown_data();
    $data = @unserialize( base64_decode($base64) );
    if( !is_array($data) ){
        return ;
    };
    
    
    foreach( $data as $field ){
        if( $field['name'] == $_REQUEST['field_name'] ){
            $nColumn = intval($_REQUEST['n']);
            $lookup  = $_REQUEST['lookup']; // $lookup is an array
            $dd      = new DependantDropdown(); 
            echo $dd->lookupFieldColumn( $field, $nColumn, $lookup );
            return;
        };
    };
    
    return;
}


function phpfmg_filman_download(){
    if( !isset($_REQUEST['filelink']) )
        return ;
        
    $info =  @unserialize(base64_decode($_REQUEST['filelink']));
    if( !isset($info['recordID']) ){
        return ;
    };
    
    $file = PHPFMG_SAVE_ATTACHMENTS_DIR . $info['recordID'] . '-' . $info['filename'];
    phpfmg_util_download( $file, $info['filename'] );
}


class phpfmgDataManager
{
    var $dataFile = '';
    var $columns = '';
    var $records = '';
    
    function phpfmgDataManager(){
        $this->dataFile = PHPFMG_SAVE_FILE; 
    }
    
    function parseFile(){
        $fp = @fopen($this->dataFile, 'rb');
        if( !$fp ) return false;
        
        $i = 0 ;
        $phpExitLine = 1; // first line is php code
        $colsLine = 2 ; // second line is column headers
        $this->columns = array();
        $this->records = array();
        $sep = chr(0x09);
        while( !feof($fp) ) { 
            $line = fgets($fp);
            $line = trim($line);
            if( empty($line) ) continue;
            $line = $this->line2display($line);
            $i ++ ;
            switch( $i ){
                case $phpExitLine:
                    continue;
                    break;
                case $colsLine :
                    $this->columns = explode($sep,$line);
                    break;
                default:
                    $this->records[] = explode( $sep, phpfmg_data2record( $line, false ) );
            };
        }; 
        fclose ($fp);
    }
    
    function displayRecords(){
        $this->parseFile();
        echo "<table border=1 style='width=95%;border-collapse: collapse;border-color:#cccccc;' >";
        echo "<tr><td>&nbsp;</td><td><b>" . join( "</b></td><td>&nbsp;<b>", $this->columns ) . "</b></td></tr>\n";
        $i = 1;
        foreach( $this->records as $r ){
            echo "<tr><td align=right>{$i}&nbsp;</td><td>" . join( "</td><td>&nbsp;", $r ) . "</td></tr>\n";
            $i++;
        };
        echo "</table>\n";
    }
    
    function line2display( $line ){
        $line = str_replace( array('"' . chr(0x09) . '"', '""'),  array(chr(0x09),'"'),  $line );
        $line = substr( $line, 1, -1 ); // chop first " and last "
        return $line;
    }
    
}
# end of class



# ------------------------------------------------------
class phpfmgImage
{
    var $im = null;
    var $width = 73 ;
    var $height = 33 ;
    var $text = '' ; 
    var $line_distance = 8;
    var $text_len = 4 ;

    function phpfmgImage( $text = '', $len = 4 ){
        $this->text_len = $len ;
        $this->text = '' == $text ? $this->uniqid( $this->text_len ) : $text ;
        $this->text = strtoupper( substr( $this->text, 0, $this->text_len ) );
    }
    
    function create(){
        $this->im = imagecreate( $this->width, $this->height );
        $bgcolor   = imagecolorallocate($this->im, 255, 255, 255);
        $textcolor = imagecolorallocate($this->im, 0, 0, 0);
        $this->drawLines();
        imagestring($this->im, 5, 20, 9, $this->text, $textcolor);
    }
    
    function drawLines(){
        $linecolor = imagecolorallocate($this->im, 210, 210, 210);
    
        //vertical lines
        for($x = 0; $x < $this->width; $x += $this->line_distance) {
          imageline($this->im, $x, 0, $x, $this->height, $linecolor);
        };
    
        //horizontal lines
        for($y = 0; $y < $this->height; $y += $this->line_distance) {
          imageline($this->im, 0, $y, $this->width, $y, $linecolor);
        };
    }
    
    function out( $filename = '' ){
        if( function_exists('imageline') ){
            $this->create();
            if( '' == $filename ) header("Content-type: image/png");
            ( '' == $filename ) ? imagepng( $this->im ) : imagepng( $this->im, $filename );
            imagedestroy( $this->im ); 
        }else{
            $this->out_predefined_image(); 
        };
    }

    function uniqid( $len = 0 ){
        $md5 = md5( uniqid(rand()) );
        return $len > 0 ? substr($md5,0,$len) : $md5 ;
    }
    
    function out_predefined_image(){
        header("Content-type: image/png");
        $data = $this->getImage(); 
        echo base64_decode($data);
    }
    
    // Use predefined captcha random images if web server doens't have GD graphics library installed  
    function getImage(){
        $images = array(
			'70F0' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7QkMZAlhDA1pRRFsZQ1gbGKY6oIixtgLFAgKQxaaINLo2MDqIILsvatrK1NCVWdOQ3AdUgawODFkbMMVEGjDtCGjAdAtQPgAohurmAQo/KkIs7gMAZprK1RV3AzAAAAAASUVORK5CYII=',
			'A41D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7GB0YWhmmMIY6IImxBjBMZQhhdAhAEhOZwhDKCBQTQRILaGV0BeqFiYGdFLV06dJV01ZmTUNyX0CrSCuSOjAMDRUNdZiCbh4DhjqYWACaGGOoI4qbByr8qAixuA8A4zjKw0MkJDcAAAAASUVORK5CYII=',
			'49D5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAb0lEQVR4nGNYhQEaGAYTpI37pjCGsIYyhgYgi4WwtrI2Ojogq2MMEWl0bQhEEWOdAhZzdUBy37RpS5emroqMikJyX8AUxkDXhoAGESS9oaEMjehiDFNYwHagioHc4hCA4j6wmxmmOgyG8KMexOI+AERZzIZul35CAAAAAElFTkSuQmCC',
			'4E13' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpI37poiGMkxhCHVAFgsRAWJGhwAkMUagGGMIQ4MIkhjrFCBvCkNDAJL7pk2bGrZq2qqlWUjuC0BVB4ahoRAxERS34BJDdQvIzYyhDqhuHqjwox7E4j4AXAnLurzw+Y0AAAAASUVORK5CYII=',
			'D28C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7QgMYQxhCGaYGIIkFTGFtZXR0CBBBFmsVaXRtCHRgQRFjaHR0dHRAdl/U0lVLV4WuzEJ2H1DdFEaEOphYACvQPFQxRgdWdDumsDaguyU0QDTUAc3NAxV+VIRY3AcAg+3MaWbhpBgAAAAASUVORK5CYII=',
			'3CD3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAX0lEQVR4nGNYhQEaGAYTpIn7RAMYQ1lDGUIdkMQCprA2ujY6OgQgq2wVaXBtCGgQQRabItLAChQLQHLfyqhpq5auilqahew+VHVw81jRzcNiBza3YHPzQIUfFSEW9wEAu73OPCVvIZIAAAAASUVORK5CYII=',
			'E89A' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7QkMYQxhCGVqRxQIaWFsZHR2mOqCIiTS6NgQEBKCpY20IdBBBcl9o1MqwlZmRWdOQ3AdSxxACVwc3z6EhMDQETcyxAV0dyC2OKGIQNzOiiA1U+FERYnEfAOl2zKFJsAZqAAAAAElFTkSuQmCC',
			'FF60' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXElEQVR4nGNYhQEaGAYTpIn7QkNFQx1CGVqRxQIaRBoYHR2mOqCJsTY4BARgiDE6iCC5LzRqatjSqSuzpiG5D6zO0RGmDklvIBaxAAw7sLmFAc3NAxV+VIRY3AcAjbbNV1O8/u8AAAAASUVORK5CYII=',
			'DD29' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7QgNEQxhCGaY6IIkFTBFpZXR0CAhAFmsVaXRtCHQQQRNzQIiBnRS1dNrKrJVZUWFI7gOra2WYiqF3CkMDhlgAA6odILc4MKC4BeRm1tAAFDcPVPhREWJxHwCU584T6xqvSQAAAABJRU5ErkJggg==',
			'E4E5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7QkMYWllDHUMDkMSA7KmsDYwODKhioZhijK5AMVcHJPeFRi1dujR0ZVQUkvsCGkRaWYG0CIpe0VBXDDGgW4B2YIoxBCC7D+Jmh6kOgyD8qAixuA8AMiDLm+nTuggAAAAASUVORK5CYII=',
			'30B9' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7RAMYAlhDGaY6IIkFTGEMYW10CAhAVtnK2sraEOgggiw2RaTRtdERJgZ20sqoaStTQ1dFhSG7D6zOYSqK3lagWENAgwiGHQEodmBzCzY3D1T4URFicR8AIQLMN2tpl3UAAAAASUVORK5CYII=',
			'B7A2' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcklEQVR4nGNYhQEaGAYTpIn7QgNEQx2mMEx1QBILmMLQ6BDKEBCALNbK0Ojo6OgggqqulbUhoEEEyX2hUaumLV0VBYQI9wHVBQDVNaLY0crowBoKNBVFjLWBFaQaxQ4RkFgAqptBYoGhIYMg/KgIsbgPADLMzp/nMMrHAAAAAElFTkSuQmCC',
			'B206' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcklEQVR4nGNYhQEaGAYTpIn7QgMYQximMEx1QBILmMLayhDKEBCALNYq0ujo6OgggKKOodG1IdAB2X2hUauWLl0VmZqF5D6guimsDYFo5jEEAMUcRFDEGB0YgXaIoLqlAd0toQGioQ5obh6o8KMixOI+ADa7zP5QxxuBAAAAAElFTkSuQmCC',
			'4BA9' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdUlEQVR4nGNYhQEaGAYTpI37poiGMExhmOqALBYi0soQyhAQgCTGGCLS6Ojo6CCCJMY6RaSVtSEQJgZ20rRpU8OWroqKCkNyXwBYXcBUZL2hoSKNrqEBDSIobgGKNQQ4oImB9KK4BeRmkHkobh6o8KMexOI+AKCnzPNMUoGoAAAAAElFTkSuQmCC',
			'237F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7WANYQ1hDA0NDkMREpoi0MjQEOiCrC2hlaHRAE2NoBcJGR5gYxE3TVoWtWroyNAvZfQFAdVMYUfQCeY0OAahirA0g01DFRBpEWlkbUMVCQ4FuRhMbqPCjIsTiPgCyQMkKlYXewQAAAABJRU5ErkJggg==',
			'7966' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpIn7QkMZQxhCGaY6IIu2srYyOjoEBKCIiTS6Njg6CCCLTQGJMTqguC9q6dLUqStTs5Dcx+jAGOjq6IhiHmsDA1BvoIMIkphIAwuGWEADplsCGrC4eYDCj4oQi/sAcA7LvFUUlioAAAAASUVORK5CYII=',
			'3AB6' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7RAMYAlhDGaY6IIkFTGEMYW10CAhAVtnK2sraEOgggCw2RaTRtdHRAdl9K6OmrUwNXZmahew+iDo080RDXYHmiaCIAdWhiQWA9aK6RTQAKIbm5oEKPypCLO4DAOqWzRyNVoJ1AAAAAElFTkSuQmCC',
			'2F99' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbElEQVR4nGNYhQEaGAYTpIn7WANEQx1CGaY6IImJTBFpYHR0CAhAEgtoFWlgbQh0EEHWjSoGcdO0qWErM6OiwpDdFwBUERIwFVkvI1hXQAOyGCuQx9gQgGKHSAOmW0JDgSrQ3DxQ4UdFiMV9AAaWy0mk9V7KAAAAAElFTkSuQmCC',
			'CC08' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7WEMYQxmmMEx1QBITaWVtdAhlCAhAEgtoFGlwdHR0EEEWaxBpYG0IgKkDOylq1bRVS1dFTc1Cch+aOiSxQFTzsNiBzS3Y3DxQ4UdFiMV9AI5ZzUeTf2seAAAAAElFTkSuQmCC',
			'C4A9' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdElEQVR4nGNYhQEaGAYTpIn7WEMYWhmmMEx1QBITaWWYyhDKEBCAJBbQyBDK6OjoIIIs1sDoytoQCBMDOylq1dKlS1dFRYUhuS8AaCJrQ8BUVL2ioa6hQBlUO0DqUOwAugUkhuIWkJtB5iG7eaDCj4oQi/sAkovM0jRg26gAAAAASUVORK5CYII=',
			'72F9' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAb0lEQVR4nGNYhQEaGAYTpIn7QkMZQ1hDA6Y6IIu2srayNjAEBKCIiTS6NjA6iCCLTWFAFoO4KWrV0qWhq6LCkNzH6MAwBWjeVGS9IPOBuAFZTASoEiiGYkcAUCW6WwIaRENdgeahuHmAwo+KEIv7AJOEyvRrbWroAAAAAElFTkSuQmCC',
			'94C4' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nM2QrRHAIAxGg4hH0H0w+IhimCYV2QC6AYYpS13aItsr+dy7/LwLtEcxzJRP/JBAIHomxVyGYjxtmlHvQrZyZSYgQyblt5daa2spKT8MTpD7Rn1ZlhjYxFUxK9D77N1FzknNRs5//e/FDPwOQ1fNBu81zxwAAAAASUVORK5CYII=',
			'1D8C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAX0lEQVR4nGNYhQEaGAYTpIn7GB1EQxhCGaYGIImxOoi0Mjo6BIggiYk6iDS6NgQ6sKDoFWl0dHR0QHbfyqxpK7NCV2Yhuw9NHVwMZB42MTQ7MN0SgunmgQo/KkIs7gMA8mfI3/taoOkAAAAASUVORK5CYII=',
			'915D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7WAMYAlhDHUMdkMREpjAGsDYwOgQgiQW0soLFRFDEgHqnwsXATpo2dVXU0szMrGlI7mN1ZQhgaAhE0cvQiikmADIPTUxkCkMAo6MjiluALgllCGVEcfNAhR8VIRb3AQB6mMhaxKu7/QAAAABJRU5ErkJggg==',
			'055C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdklEQVR4nGNYhQEaGAYTpIn7GB1EQ1lDHaYGIImxBog0sDYwAEmEmMgUkBijAwuSWECrSAjrVEYHZPdFLZ26dGlmZhay+wJaGRodGgIdGFD0YooB7Wh0BYoh28EawNrK6OiA4hZGB8YQhlAGFDcPVPhREWJxHwAvksqj8anAywAAAABJRU5ErkJggg==',
			'0114' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpIn7GB0YAhimMDQEIImxBjAGMIQwNCKLiUwBioYwtCKLBbSC9U4JQHJf1NJVUaumrYqKQnIfRB2jA6ZextAQFDuwuQVTjNGBNZQx1AFFbKDCj4oQi/sA8BHKg7dfm5wAAAAASUVORK5CYII=',
			'58D5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7QkMYQ1hDGUMDkMQCGlhbWRsdHRhQxEQaXRsCUcQCA4DqGgJdHZDcFzZtZdjSVZFRUcjuawWpA5qAbHMryDxUsYBWiB3IYiJTQG5xCEB2H2sAyM0MUx0GQfhREWJxHwC62syyTPS6fwAAAABJRU5ErkJggg==',
			'44E3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZklEQVR4nGNYhQEaGAYTpI37pjC0soY6hDogi4UwTGVtYHQIQBJjDGEIZQXSIkhirFMYXUFiAUjumzZt6dKloauWZiG5L2CKSCuSOjAMDRUNdUUzD+wWrGKobsHq5oEKP+pBLO4DAEKHy5rLBXwmAAAAAElFTkSuQmCC',
			'967E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7WAMYQ1hDA0MDkMREprC2MjQEOiCrC2gVacQi1sDQ6AgTAztp2tRpYauWrgzNQnIfq6toK8MURhS9DEDzHAJQxQSAYo4OqGIgt7A2oIqB3dzAiOLmgQo/KkIs7gMAR0bJmRzVLO0AAAAASUVORK5CYII=',
			'3534' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAb0lEQVR4nGNYhQEaGAYTpIn7RANEQxlDGRoCkMQCpog0sDY6NCKLMbSKgGRaUcSmiIQwNDpMCUBy38qoqUtXTV0VFYXsvilAVY2ODqjmAcUaAkNDUO0AigWguYW1lRUsiuxmxhB0Nw9U+FERYnEfAF8rzs1TVvxTAAAAAElFTkSuQmCC',
			'6C5E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpIn7WAMYQ1lDHUMDkMREprA2ujYwOiCrC2gRacAQaxBpYJ0KFwM7KTJq2qqlmZmhWUjuC5kiAiQDUfW2YhdzRRMDucXR0RFFDORmhlBGFDcPVPhREWJxHwCGrcrdIzDu1QAAAABJRU5ErkJggg==',
			'9246' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAeklEQVR4nGNYhQEaGAYTpIn7WAMYQxgaHaY6IImJTGFtZWh1CAhAEgtoFQGqcnQQQBED6gx0dEB237Spq5auzMxMzUJyH6srwxTWRkcU8xhaGQJYQwMdRJDEBFoZHRgaHVHEgG5pANqCopc1QDTUAc3NAxV+VIRY3AcAZ0vMRmeg0OsAAAAASUVORK5CYII=',
			'FAC5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7QkMZAhhCHUMDkMQCGhhDGB0CHRhQxFhbWRsE0cREGl0bGF0dkNwXGjVtZeqqlVFRSO6DqGNoEEHRKxqKKQZSJ+iALuboEBAQgCbmEOow1WEQhB8VIRb3AQApnM12lJUX/gAAAABJRU5ErkJggg==',
			'FCF3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAWklEQVR4nGNYhQEaGAYTpIn7QkMZQ1lDA0IdkMQCGlgbXRsYHQJQxEQaXIG0CJoYK5hGuC80atqqpaGrlmYhuQ9NHYoYunmYdmBzC9DNDQwobh6o8KMixOI+AK2Zzi3V7fHmAAAAAElFTkSuQmCC',
			'44AD' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpI37pjC0MkxhDHVAFgthmMoQyugQgCTGGAIUcXR0EEESY53C6MraEAgTAztp2rSlS5euisyahuS+gCkirUjqwDA0VDTUNRRVDOQWdHUwsQBMMVQ3D1T4UQ9icR8AkDXLRVIWG6QAAAAASUVORK5CYII=',
			'3B38' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAWUlEQVR4nGNYhQEaGAYTpIn7RANEQxhDGaY6IIkFTBFpZW10CAhAVtkq0ujQEOgggiwGVMeAUAd20sqoqWGrpq6amoXsPlR1uM3DIobNLdjcPFDhR0WIxX0AutTNcpj6PZUAAAAASUVORK5CYII=',
			'059B' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7GB1EQxlCGUMdkMRYA0QaGB0dHQKQxESmiDSwNgQ6iCCJBbSKhIDEApDcF7V06tKVmZGhWUjuC2hlaHQICUQxDyyGZh7QjkZHNDHWANZWdLcwOjCGoLt5oMKPihCL+wB/7crlRh4TYgAAAABJRU5ErkJggg==',
			'EAC0' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZElEQVR4nGNYhQEaGAYTpIn7QkMYAhhCHVqRxQIaGEMYHQKmOqCIsbayNggEBKCIiTS6NjA6iCC5LzRq2srUVSuzpiG5D00dVEw0FFMMpA7TDkc0t4SGiDQ6oLl5oMKPihCL+wD+pM3tIWejvgAAAABJRU5ErkJggg==',
			'2564' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdElEQVR4nM3QMQ6AIAyF4TJwg3ofGNxrQhdP8xy4AXoDF04pY1FHjbbbnzT5UqqXAf1pX/F5GZSUIKZxYbgYFtskMzxCto0yJw8qYn3buu9rnWfrE1rGGIO9daE1TJqsBdya9Bb43CxdU3XpbP7qfw/uje8Aly7NZVVAaNAAAAAASUVORK5CYII=',
			'F666' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYklEQVR4nGNYhQEaGAYTpIn7QkMZQxhCGaY6IIkFNLC2Mjo6BASgiIk0sjY4OgigijWwNjA6ILsvNGpa2NKpK1OzkNwX0CDayuroiGGea0OggwhBMWxuwXTzQIUfFSEW9wEA0DHM3K4Ru2QAAAAASUVORK5CYII=',
			'3A8A' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7RAMYAhhCGVqRxQKmMIYwOjpMdUBW2craytoQEBCALDZFpNHR0dFBBMl9K6OmrcwKXZk1Ddl9qOqg5omGujYEhoagiIk0AsVQ1AVg0SsaINLoEMqIat4AhR8VIRb3AQByi8u+K0r0RQAAAABJRU5ErkJggg==',
			'406F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpI37pjAEMIQyhoYgi4UwhjA6Ojogq2MMYW1lbUAVY50i0ujawAgTAztp2rRpK1OnrgzNQnJfAEgdmnmhoSC9gQ6obgHZgS6G6Raom1HFBir8qAexuA8AF+rI78Cqr+EAAAAASUVORK5CYII=',
			'1E84' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7GB1EQxlCGRoCkMRYHUQaGB0dGpHFRIFirA0BrQEoesHqpgQguW9l1tSwVaGroqKQ3AdR5+iArpe1ITA0BEMsoAGLHShioiGYbh6o8KMixOI+AKqQykVVLEU1AAAAAElFTkSuQmCC',
			'71F2' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7QkMZAlhDA6Y6IIu2MgawNjAEBKCIsQLFGB1EkMWmMIDUNYgguy9qVdTSUBCFcB+jA1hdI7IdIPOBuBXZLSIQsSnIYgEQsQBUMdZQoFtCQwZB+FERYnEfAFznySEvBh3nAAAAAElFTkSuQmCC',
			'9542' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAeklEQVR4nGNYhQEaGAYTpIn7WANEQxkaHaY6IImJTBFpYGh1CAhAEgtoBYpNdXQQQRULYQh0aBBBct+0qVOXrszMWhWF5D5WV4ZG10aHRmQ7GFqBYqEBrchuEWgVAamawoDiFlagSocAVDczhjA0OoaGDILwoyLE4j4Al0rNObJQ01IAAAAASUVORK5CYII=',
			'F993' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAX0lEQVR4nGNYhQEaGAYTpIn7QkMZQxhCGUIdkMQCGlhbGR0dHQJQxEQaXUEkFrEAJPeFRi1dmpkZtTQLyX0BDYyBDiFwdVAxhkYHDPNYGh0xxLC5BdPNAxV+VIRY3AcA8KbObgYtfuYAAAAASUVORK5CYII=',
			'CF2E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7WENEQx1CGUMDkMREWkUaGB0dHZDVBTSKNLA2BKKKNYgASbgY2ElRq6aGrVqZGZqF5D6wulZGTL1TGDHsYAhAFQO7xQFVjDUE6JbQQBQ3D1T4URFicR8AYlvJ2Hn8NcoAAAAASUVORK5CYII=',
			'CC9C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7WEMYQxlCGaYGIImJtLI2Ojo6BIggiQU0ijS4NgQ6sCCLNYg0sALFkN0XtWraqpWZkVnI7gOpYwiBq0OINaCJAe1wRLMDm1uwuXmgwo+KEIv7ABkIzBzeazX5AAAAAElFTkSuQmCC',
			'5B06' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpIn7QkNEQximMEx1QBILaBBpZQhlCAhAFWt0dHR0EEASCwwQaWVtCHRAdl/YtKlhS1dFpmYhu68VrA7FPKBYoytQrwiyHa0QO5DFRKZguoU1ANPNAxV+VIRY3AcAR0zMMPt2abEAAAAASUVORK5CYII=',
			'E1BE' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAV0lEQVR4nGNYhQEaGAYTpIn7QkMYAlhDGUMDkMQCGhgDWBsdHRhQxFgDWBsC0cQYkNWBnRQatSpqaejK0Cwk96GpQ4hhMw+/HVA3s4aiu3mgwo+KEIv7APa8yaD08FTHAAAAAElFTkSuQmCC',
			'493F' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpI37pjCGMIYyhoYgi4WwtrI2Ojogq2MMEWl0aAhEEWOdAhRDqAM7adq0pUuzpq4MzUJyX8AUxkAHNPNCQxkwzGOYwoJFDNMtUDejig1U+FEPYnEfAEo7yqRDplAiAAAAAElFTkSuQmCC',
			'5810' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7QkMYQximMLQiiwU0sLYyhDBMdUARE2l0DGEICEASCwwAqpvC6CCC5L6waSvDVk1bmTUN2X2tKOqgYiKNDmhiAWAxVDtEpoD0orqFNYAxhDHUAcXNAxV+VIRY3AcA0QfL4Aq0FmoAAAAASUVORK5CYII=',
			'F0DB' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAWUlEQVR4nGNYhQEaGAYTpIn7QkMZAlhDGUMdkMQCGhhDWBsdHQJQxFhbWRsCHURQxEQaXYFiAUjuC42atjJ1VWRoFpL70NShiIkQtAObWzDdPFDhR0WIxX0Ab0LNP3m0Tz4AAAAASUVORK5CYII=',
			'20C5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcElEQVR4nGNYhQEaGAYTpIn7WAMYAhhCHUMDkMREpjCGMDoEOiCrC2hlbWVtEEQRY2gVaXRtYHR1QHbftGkrU1etjIpCdl8ASB3QXCS9jA6YYqwNEDuQxUQaQG4JCEB2X2goyM0OUx0GQfhREWJxHwC8JMpjLe24mwAAAABJRU5ErkJggg==',
			'6C0D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYklEQVR4nGNYhQEaGAYTpIn7WAMYQxmmMIY6IImJTGFtdAhldAhAEgtoEWlwdHR0EEEWaxBpYG0IhImBnRQZNW3V0lWRWdOQ3BcyBUUdRG8rdjF0O7C5BZubByr8qAixuA8A6+LL/gRvuEEAAAAASUVORK5CYII=',
			'CC5C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAb0lEQVR4nGNYhQEaGAYTpIn7WEMYQ1lDHaYGIImJtLI2ujYwBIggiQU0ijS4NjA6sCCLNYg0sE5ldEB2X9SqaauWZmZmIbsPpI6hIdCBAU0vhhjYjkAUO0BucXR0QHELyM0MoQwobh6o8KMixOI+ABFqzBX7Yk8MAAAAAElFTkSuQmCC',
			'C0AA' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZklEQVR4nGNYhQEaGAYTpIn7WEMYAhimMLQii4m0MoYwhDJMdUASC2hkbWV0dAgIQBZrEGl0bQh0EEFyX9SqaStTV0VmTUNyH5o6hFhoYGgImh2saOpAbkEXA7kZXWygwo+KEIv7AEgPzFRYsRlAAAAAAElFTkSuQmCC',
			'7B0B' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7QkNFQximMIY6IIu2irQyhDI6BKCKNTo6OjqIIItNEWllbQiEqYO4KWpq2NJVkaFZSO5jdEBRB4asDSKNrkAxZPNEGjDtCGjAdEtAAxY3D1D4URFicR8A/XbLXG5cB94AAAAASUVORK5CYII=',
			'DA04' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbUlEQVR4nGNYhQEaGAYTpIn7QgMYAhimMDQEIIkFTGEMYQhlaEQRa2VtZXR0aEUVE2l0BaoOQHJf1NJpK1NXRUVFIbkPoi7QAVWvaChQLDQEzTxHRwc0t4g0OoSiui80ACiG5uaBCj8qQizuAwDdatA9Jd8yZwAAAABJRU5ErkJggg==',
			'67BE' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7WANEQ11DGUMDkMREpjA0ujY6OiCrC2gBijUEooo1MLSyItSBnRQZtWra0tCVoVlI7guZwhDAim5eK6MDK7p5rawN6GIiU0Qa0PWyBgDF0Nw8UOFHRYjFfQD8KcsdO0langAAAABJRU5ErkJggg==',
			'6319' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nM2QwQ2AIAxFPwc3YKC6AQckkWkgkQ2KQzClwsGU4FGj/beXNv+lKMME/Cmv+E1msmBkEkyzTrAwRjCzIc5WkZYsIIEv1pRWX1zZi3fCz3LdQ+5uEyLx2TWyrqO5cO9SndVCnfNX/3swN34Hhu/L1csFFmkAAAAASUVORK5CYII=',
			'CFCD' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAX0lEQVR4nGNYhQEaGAYTpIn7WENEQx1CHUMdkMREWkUaGB0CHQKQxAIaRRpYGwQdRJDFGkBijDAxsJOiVk0NW7pqZdY0JPehqcMthsUObG5hDQGqQHPzQIUfFSEW9wEAR/fLbhW+1D8AAAAASUVORK5CYII=',
			'51D0' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZElEQVR4nGNYhQEaGAYTpIn7QkMYAlhDGVqRxQIaGANYGx2mOqCIsQawNgQEBCCJBQYA9TYEOogguS9s2qqopasis6Yhu68VRR1OsQCwGKodIlMYMNwCdEkoupsHKvyoCLG4DwC05crltR2TdwAAAABJRU5ErkJggg==',
			'4E20' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAc0lEQVR4nGNYhQEaGAYTpI37poiGMoQytKKIhYg0MDo6THVAEmMEirE2BAQEIImxThEBkoEOIkjumzZtatiqlZlZ05DcFwBS18oIUweGoaFA3hRUMQaQugAGFDtAYowODChuAbmZNTQA1c0DFX7Ug1jcBwB+Tcrh3xDziQAAAABJRU5ErkJggg==',
			'BCF9' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZ0lEQVR4nGNYhQEaGAYTpIn7QgMYQ1lDA6Y6IIkFTGFtdG1gCAhAFmsVaXBtYHQQQVEn0sCKEAM7KTRq2qqloauiwpDcB1HHMFUEzTygWAO6GNBeNDsw3QJ2M9A8ZDcPVPhREWJxHwDPfM16FkrozAAAAABJRU5ErkJggg==',
			'4A1E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAX0lEQVR4nGNYhQEaGAYTpI37pjAEMExhDA1AFgthDAFiB2R1jCGsrYxoYqxTRBodpsDFwE6aNm3ayqxpK0OzkNwXgKoODENDRUPRxRiwqMMl5hjqiOrmgQo/6kEs7gMA5/TKCjFBLqoAAAAASUVORK5CYII=',
			'45EB' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAZUlEQVR4nGNYhQEaGAYTpI37poiGsoY6hjogi4WINLA2MDoEIIkxQsVEkMRYp4iEIKkDO2natKlLl4auDM1Ccl/AFIZGVzTzQkMhYiIobhHBIsbaiu4WhimMIRhuHqjwox7E4j4AGvjKqaQHOloAAAAASUVORK5CYII=',
			'6C68' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7WAMYQxlCGaY6IImJTGFtdHR0CAhAEgtoEWlwbXB0EEEWaxBpYG1ggKkDOykyatqqpVNXTc1Ccl/IFKA6dPNaQXoDUc1rBdmBKobNLdjcPFDhR0WIxX0AdlXNPhsBceQAAAAASUVORK5CYII=',
			'EC86' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAWUlEQVR4nGNYhQEaGAYTpIn7QkMYQxlCGaY6IIkFNLA2Ojo6BASgiIk0uDYEOgigiTECFSK7LzRqGpBYmZqF5D6oOgzzWIHmiWCxQ4SAW7C5eaDCj4oQi/sAe07NR2NXENYAAAAASUVORK5CYII=',
			'2AD1' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7WAMYAlhDGVqRxUSmMIawNjpMRRYLaGVtZW0ICEXR3SrS6AqUQXHftGkrU1dFLUVxXwCKOjBkdBANRRdjbcBUJwISa3RAEQsNBYqFMoQGDILwoyLE4j4AGZPNRZgpnx0AAAAASUVORK5CYII=',
			'1FF3' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAWElEQVR4nGNYhQEaGAYTpIn7GB1EQ11DA0IdkMRYHUQaWIEyAUhiomAxhgYRFL0QsQAk963Mmhq2NHTV0iwk96GpQxHDZh6mGJpbQsDqUNw8UOFHRYjFfQCTzclJ1zZc7QAAAABJRU5ErkJggg==',
			'BEE6' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAVUlEQVR4nGNYhQEaGAYTpIn7QgNEQ1lDHaY6IIkFTBFpYG1gCAhAFmsFiTE6CGCoY3RAdl9o1NSwpaErU7OQ3AdVh9U8EUJiWNyCzc0DFX5UhFjcBwAmLswuml4WUgAAAABJRU5ErkJggg==',
			'4243' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAeElEQVR4nGNYhQEaGAYTpI37pjCGMDQ6hDogi4WwtjK0OjoEIIkxhog0Okx1aBBBEmOdAtQZ6NAQgOS+adNWLV2ZmbU0C8l9AVMYprA2wtWBYWgoQwBraACKeUC3OABNRBNjbWBoRHULwxTRUAd0Nw9U+FEPYnEfALyjzYH/eoTgAAAAAElFTkSuQmCC',
			'F0BE' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAV0lEQVR4nGNYhQEaGAYTpIn7QkMZAlhDGUMDkMQCGhhDWBsdHRhQxFhbWRsC0cREGl0R6sBOCo2atjI1dGVoFpL70NQhxDDMw2YHNrdgunmgwo+KEIv7AJCBy8Brc1KBAAAAAElFTkSuQmCC',
			'DA0C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7QgMYAhimMEwNQBILmMIYwhDKECCCLNbK2sro6OjAgiIm0ujaEOiA7L6opdNWpq6KzEJ2H5o6qJhoKKaYSKMjuh1TRBod0NwSGgAUQ3PzQIUfFSEW9wEAbVLNdkHxlEMAAAAASUVORK5CYII=',
			'DA41' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7QgMYAhgaHVqRxQKmMIYwtDpMRRFrZW1lmOoQiiom0ugQCNcLdlLU0mkrMzOzliK7D6TOFd2OVtFQ19CAVgzzMNyCKRYaABYLDRgE4UdFiMV9AAnjz51hyGSPAAAAAElFTkSuQmCC',
			'A978' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAd0lEQVR4nGNYhQEaGAYTpIn7GB0YQ1hDA6Y6IImxBrC2MjQEBAQgiYlMEWl0aAh0EEESC2gFijU6wNSBnRS1dOnSrKWrpmYhuS+glTHQYQoDinmhoQxAnYxo5rE0Ojqgi7G2sjag6gWaFwIUQ3HzQIUfFSEW9wEAffvNQKOO0zAAAAAASUVORK5CYII=',
			'4832' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAa0lEQVR4nGNYhQEaGAYTpI37pjCGMIYyTHVAFgthbWVtdAgIQBJjDBFpdGgIdBBBEmOdwtrKABQVQXLftGkrw1ZNXbUqCsl9ARB1jch2hIaCzAtoRXULWGwKqhjELZhuZgwNGQzhRz2IxX0AVS/NMJN1I7QAAAAASUVORK5CYII=',
			'E892' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7QkMYQxhCGaY6IIkFNLC2Mjo6BASgiIk0ujYEOoigqWMFySC5LzRqZdjKzKhVUUjuA6ljCAlodEAzz6EhoJUBTcyxIWAKAxa3YLqZMTRkEIQfFSEW9wEA6VjNi3ztWAEAAAAASUVORK5CYII=',
			'A17D' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7GB0YAlhDA0MdkMRYAxgDGBoCHQKQxESmsILFRJDEAloZAhgaHWFiYCdFLV0VtWrpyqxpSO4Dq5vCiKI3NBQoFsCIYR6jA6YYK9CVAShirKFAMRQ3D1T4URFicR8ApojJaGaD+XsAAAAASUVORK5CYII=',
			'5892' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAd0lEQVR4nGNYhQEaGAYTpIn7QkMYQxhCGaY6IIkFNLC2Mjo6BASgiIk0ujYEOoggiQUGsLaygmSQ3Bc2bWXYysyoVVHI7mtlbWUICWhEtoOhVQTID2hFdksAUMyxIWAKspjIFIhbkMVYA0BuZgwNGQThR0WIxX0A1njMjLjlyNAAAAAASUVORK5CYII=',
			'EB13' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7QkNEQximMIQ6IIkFNIi0MoQwOgSgijU6hjA0iKCrmwKiEe4LjZoatmraqqVZSO5DUwc3z2EKhnnYxIB6Ud0CcjNjqAOKmwcq/KgIsbgPAD9gzfR8B2JoAAAAAElFTkSuQmCC',
			'1EEB' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAVElEQVR4nGNYhQEaGAYTpIn7GB1EQ1lDHUMdkMRYHUQaWIEyAUhiolAxERS9KOrATlqZNTVsaejK0Cwk9zFiMY8Rj3l47IC4JQTTzQMVflSEWNwHABfDx1ouHS8pAAAAAElFTkSuQmCC',
			'447C' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAdElEQVR4nGNYhQEaGAYTpI37pjC0soYGTA1AFgthmMrQEBAggiTGGMIQytAQ6MCCJMY6hdGVodHRAdl906YtXbpq6cosZPcFTBFpZZjC6IBsb2ioaKhDAKoYyC2MDowodoDd18CA4haoGKqbByr8qAexuA8AQxfKjYbMT6QAAAAASUVORK5CYII=',
			'09B9' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7GB0YQ1hDGaY6IImxBrC2sjY6BAQgiYlMEWl0bQh0EEESC2gFijU6wsTATopaunRpauiqqDAk9wW0Mga6NjpMRdXLADQvoEEExQ4WkBiKHdjcgs3NAxV+VIRY3AcAl1XMk/5u4JEAAAAASUVORK5CYII=',
			'D3E2' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYElEQVR4nGNYhQEaGAYTpIn7QgNYQ1hDHaY6IIkFTBFpZW1gCAhAFmtlaHRtYHQQQRUDqWsQQXJf1NJVYUtDgTSS+6DqGh0wzGNoZcAUm8KAxS2YbnYMDRkE4UdFiMV9AMaYzWd7VMexAAAAAElFTkSuQmCC',
			'2B40' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcklEQVR4nGNYhQEaGAYTpIn7WANEQxgaHVqRxUSmiLQytDpMdUASC2gVaQSKBAQg624Fqgt0dBBBdt+0qWErMzOzpiG7L0CklbURrg4MGR1EGl1DA1HEWBuAdjSi2iHSALSjEdUtoaGYbh6o8KMixOI+AAPkzOP8sVwQAAAAAElFTkSuQmCC',
			'DB21' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAYUlEQVR4nGNYhQEaGAYTpIn7QgNEQxhCGVqRxQKmiLQyOjpMRRFrFWl0bQgIRRNrBZHI7otaOjVs1cqspcjuA6trRbMDaJ7DFCxiAVjc4oAqBnIza2hAaMAgCD8qQizuAwAP2c2u9WeTEgAAAABJRU5ErkJggg==',
			'EB19' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAY0lEQVR4nGNYhQEaGAYTpIn7QkNEQximMEx1QBILaBBpZQhhCAhAFWt0DGF0EEFXNwUuBnZSaNTUsFXTVkWFIbkPoo5hKpreRocpDA1YxLDYgeoWkJsZQx1Q3DxQ4UdFiMV9ABebzRIZ2TZdAAAAAElFTkSuQmCC',
			'D830' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAXUlEQVR4nGNYhQEaGAYTpIn7QgMYQxhDGVqRxQKmsLayNjpMdUAWaxVpdGgICAhAEWNtZWh0dBBBcl/U0pVhq6auzJqG5D40dUjmBWIRQ7MDi1uwuXmgwo+KEIv7AHo+ztqCWFqOAAAAAElFTkSuQmCC',
			'367E' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaElEQVR4nGNYhQEaGAYTpIn7RAMYQ1hDA0MDkMQCprC2MjQEOqCobBVpxBCbItLA0OgIEwM7aWXUtLBVS1eGZiG7b4poK8MURgzzHAIwxRwdUMVAbmFtQBUDu7mBEcXNAxV+VIRY3AcATjXJssJmffMAAAAASUVORK5CYII=',
			'6A2B' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAd0lEQVR4nGNYhQEaGAYTpIn7WAMYAhhCGUMdkMREpjCGMDo6OgQgiQW0sLayNgQ6iCCLNYg0OgDFApDcFxk1bWXWyszQLCT3hUwBqmtlRDWvVTTUYQojqnmtQHUBqGIiQL2ODqh6WQNEGl1DA1HcPFDhR0WIxX0Ai8jL6UpF09wAAAAASUVORK5CYII=',
			'8553' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7WANEQ1lDHUIdkMREpog0sDYwOgQgiQW0gsSAcqjqQlinAuWQ3Lc0aurSpZlZS7OQ3CcyhaHRAagK1TyImAiqHY2uaGIiU1hbGR0dUdzCGsAYwhDKgOLmgQo/KkIs7gMAYePNNtZDfCcAAAAASUVORK5CYII=',
			'6CA0' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7WAMYQxmmMLQii4lMYW10CGWY6oAkFtAi0uDo6BAQgCzWINLA2hDoIILkvsioaauWrorMmobkvpApKOogeluBYqGYYq4NASh2gNwCFENxC8jNrEDVgyH8qAixuA8AAOzN499W+8UAAAAASUVORK5CYII=',
			'A532' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAcUlEQVR4nGNYhQEaGAYTpIn7GB1EQxlDGaY6IImxBog0sDY6BAQgiYlMEQGSgQ4iSGIBrSIhDI0ODSJI7otaOnXpqqlAGsl9Aa1AVSCIpDc0FKQTKINqHkhsCqoYayvILahijCGMQFeHDILwoyLE4j4ALgnODjZnrlMAAAAASUVORK5CYII=',
			'2A38' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAbklEQVR4nGNYhQEaGAYTpIn7WAMYAhhDGaY6IImJTGEMYW10CAhAEgtoZW1laAh0EEHW3SrS6IBQB3HTtGkrs6aumpqF7L4AFHVgyOggGuqAZh5rA1AdmpgIUMwVTW9oqEijI5qbByr8qAixuA8AZfvNcvK37/sAAAAASUVORK5CYII=',
			'E6B5' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7QkMYQ1hDGUMDkMQCGlhbWRsdHRhQxEQaWRsC0cUagOpcHZDcFxo1LWxp6MqoKCT3BTSIAs1zAKpGNc8VbAK6WKCDCIZbHAKQ3QdxM8NUh0EQflSEWNwHAEywzVUgM1PdAAAAAElFTkSuQmCC',
			'62C4' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAc0lEQVR4nM2QMQ6AIAxFy9CdAe/D4t4BBjlNSeQGvQILp5Q4FXTUaP/2kv/zUmiXY/hTXvFDMgGiZ1LMCRbjKWtGu8sr2zIwhs5ASPltqdXaWkrKLwgIcl/U3QLUWQwDMx7Zzi5nUzOkJfrJ+av/PZgbvwN36c39ejGL0gAAAABJRU5ErkJggg==',
			'73C2' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaklEQVR4nGNYhQEaGAYTpIn7QkNZQxhCHaY6IIu2irQyOgQEBKCIMTS6Ngg6iCCLTWFoZQXSIsjui1oVthRMIdzH6ABW14hsB4jvCjIVSUwELCYwBVksoAHiFlQxkJsdQ0MGQfhREWJxHwDW0Mv2M5Dt6QAAAABJRU5ErkJggg==',
			'DB12' => 'iVBORw0KGgoAAAANSUhEUgAAAEkAAAAhAgMAAADoum54AAAACVBMVEX///8AAADS0tIrj1xmAAAAaUlEQVR4nGNYhQEaGAYTpIn7QgNEQximMEx1QBILmCLSyhDCEBCALNYq0ugYwugggirWCtTbIILkvqilU8NWTQPSSO6Dqmt0QDPPYQpDKwOm2BQGdLdMYQhAdzNjqGNoyCAIPypCLO4DAGPDzfwFrsuBAAAAAElFTkSuQmCC'        
        );
        $this->text = array_rand( $images );
        return $images[ $this->text ] ;    
    }
    
    function out_processing_gif(){
        $image = dirname(__FILE__) . '/processing.gif';
        $base64_image = "R0lGODlhFAAUALMIAPh2AP+TMsZiALlcAKNOAOp4ANVqAP+PFv///wAAAAAAAAAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQFCgAIACwAAAAAFAAUAAAEUxDJSau9iBDMtebTMEjehgTBJYqkiaLWOlZvGs8WDO6UIPCHw8TnAwWDEuKPcxQml0Ynj2cwYACAS7VqwWItWyuiUJB4s2AxmWxGg9bl6YQtl0cAACH5BAUKAAgALAEAAQASABIAAAROEMkpx6A4W5upENUmEQT2feFIltMJYivbvhnZ3Z1h4FMQIDodz+cL7nDEn5CH8DGZhcLtcMBEoxkqlXKVIgAAibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkphaA4W5upMdUmDQP2feFIltMJYivbvhnZ3V1R4BNBIDodz+cL7nDEn5CH8DGZAMAtEMBEoxkqlXKVIg4HibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpjaE4W5tpKdUmCQL2feFIltMJYivbvhnZ3R0A4NMwIDodz+cL7nDEn5CH8DGZh8ONQMBEoxkqlXKVIgIBibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpS6E4W5spANUmGQb2feFIltMJYivbvhnZ3d1x4JMgIDodz+cL7nDEn5CH8DGZgcBtMMBEoxkqlXKVIggEibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpAaA4W5vpOdUmFQX2feFIltMJYivbvhnZ3V0Q4JNhIDodz+cL7nDEn5CH8DGZBMJNIMBEoxkqlXKVIgYDibbK9YLBYvLtHH5K0J0IACH5BAUKAAgALAEAAQASABIAAAROEMkpz6E4W5tpCNUmAQD2feFIltMJYivbvhnZ3R1B4FNRIDodz+cL7nDEn5CH8DGZg8HNYMBEoxkqlXKVIgQCibbK9YLBYvLtHH5K0J0IACH5BAkKAAgALAEAAQASABIAAAROEMkpQ6A4W5spIdUmHQf2feFIltMJYivbvhnZ3d0w4BMAIDodz+cL7nDEn5CH8DGZAsGtUMBEoxkqlXKVIgwGibbK9YLBYvLtHH5K0J0IADs=";
        $binary = is_file($image) ? join("",file($image)) : base64_decode($base64_image); 
        header("Cache-Control: post-check=0, pre-check=0, max-age=0, no-store, no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-type: image/gif");
        echo $binary;
    }

}
# end of class phpfmgImage
# ------------------------------------------------------
# end of module : captcha


# module user
# ------------------------------------------------------
function phpfmg_user_isLogin(){
    return ( isset($_SESSION['authenticated']) && true === $_SESSION['authenticated'] );
}


function phpfmg_user_logout(){
    session_destroy();
    header("Location: admin.php");
}

function phpfmg_user_login()
{
    if( phpfmg_user_isLogin() ){
        return true ;
    };
    
    $sErr = "" ;
    if( 'Y' == $_POST['formmail_submit'] ){
        if(
            defined( 'PHPFMG_USER' ) && strtolower(PHPFMG_USER) == strtolower($_POST['Username']) &&
            defined( 'PHPFMG_PW' )   && strtolower(PHPFMG_PW) == strtolower($_POST['Password']) 
        ){
             $_SESSION['authenticated'] = true ;
             return true ;
             
        }else{
            $sErr = 'Login failed. Please try again.';
        }
    };
    
    // show login form 
    phpfmg_admin_header();
?>
<form name="frmFormMail" action="" method='post' enctype='multipart/form-data'>
<input type='hidden' name='formmail_submit' value='Y'>
<br><br><br>

<center>
<div style="width:380px;height:260px;">
<fieldset style="padding:18px;" >
<table cellspacing='3' cellpadding='3' border='0' >
	<tr>
		<td class="form_field" valign='top' align='right'>Email :</td>
		<td class="form_text">
            <input type="text" name="Username"  value="<?php echo $_POST['Username']; ?>" class='text_box' >
		</td>
	</tr>

	<tr>
		<td class="form_field" valign='top' align='right'>Password :</td>
		<td class="form_text">
            <input type="password" name="Password"  value="" class='text_box'>
		</td>
	</tr>

	<tr><td colspan=3 align='center'>
        <input type='submit' value='Login'><br><br>
        <?php if( $sErr ) echo "<span style='color:red;font-weight:bold;'>{$sErr}</span><br><br>\n"; ?>
        <a href="admin.php?mod=mail&func=request_password">I forgot my password</a>   
    </td></tr>
</table>
</fieldset>
</div>
<script type="text/javascript">
    document.frmFormMail.Username.focus();
</script>
</form>
<?php
    phpfmg_admin_footer();
}


function phpfmg_mail_request_password(){
    $sErr = '';
    if( $_POST['formmail_submit'] == 'Y' ){
        if( strtoupper(trim($_POST['Username'])) == strtoupper(trim(PHPFMG_USER)) ){
            phpfmg_mail_password();
            exit;
        }else{
            $sErr = "Failed to verify your email.";
        };
    };
    
    $n1 = strpos(PHPFMG_USER,'@');
    $n2 = strrpos(PHPFMG_USER,'.');
    $email = substr(PHPFMG_USER,0,1) . str_repeat('*',$n1-1) . 
            '@' . substr(PHPFMG_USER,$n1+1,1) . str_repeat('*',$n2-$n1-2) . 
            '.' . substr(PHPFMG_USER,$n2+1,1) . str_repeat('*',strlen(PHPFMG_USER)-$n2-2) ;


    phpfmg_admin_header("Request Password of Email Form Admin Panel");
?>
<form name="frmRequestPassword" action="admin.php?mod=mail&func=request_password" method='post' enctype='multipart/form-data'>
<input type='hidden' name='formmail_submit' value='Y'>
<br><br><br>

<center>
<div style="width:580px;height:260px;text-align:left;">
<fieldset style="padding:18px;" >
<legend>Request Password</legend>
Enter Email Address <b><?php echo strtoupper($email) ;?></b>:<br />
<input type="text" name="Username"  value="<?php echo $_POST['Username']; ?>" style="width:380px;">
<input type='submit' value='Verify'><br>
The password will be sent to this email address. 
<?php if( $sErr ) echo "<br /><br /><span style='color:red;font-weight:bold;'>{$sErr}</span><br><br>\n"; ?>
</fieldset>
</div>
<script type="text/javascript">
    document.frmRequestPassword.Username.focus();
</script>
</form>
<?php
    phpfmg_admin_footer();    
}


function phpfmg_mail_password(){
    phpfmg_admin_header();
    if( defined( 'PHPFMG_USER' ) && defined( 'PHPFMG_PW' ) ){
        $body = "Here is the password for your form admin panel:\n\nUsername: " . PHPFMG_USER . "\nPassword: " . PHPFMG_PW . "\n\n" ;
        if( 'html' == PHPFMG_MAIL_TYPE )
            $body = nl2br($body);
        mailAttachments( PHPFMG_USER, "Password for Your Form Admin Panel", $body, PHPFMG_USER, 'You', "You <" . PHPFMG_USER . ">" );
        echo "<center>Your password has been sent.<br><br><a href='admin.php'>Click here to login again</a></center>";
    };   
    phpfmg_admin_footer();
}


function phpfmg_writable_check(){
 
    if( is_writable( dirname(PHPFMG_SAVE_FILE) ) && is_writable( dirname(PHPFMG_EMAILS_LOGFILE) )  ){
        return ;
    };
?>
<style type="text/css">
    .fmg_warning{
        background-color: #F4F6E5;
        border: 1px dashed #ff0000;
        padding: 16px;
        color : black;
        margin: 10px;
        line-height: 180%;
        width:80%;
    }
    
    .fmg_warning_title{
        font-weight: bold;
    }

</style>
<br><br>
<div class="fmg_warning">
    <div class="fmg_warning_title">Your form data or email traffic log is NOT saving.</div>
    The form data (<?php echo PHPFMG_SAVE_FILE ?>) and email traffic log (<?php echo PHPFMG_EMAILS_LOGFILE?>) will be created automatically when the form is submitted. 
    However, the script doesn't have writable permission to create those files. In order to save your valuable information, please set the directory to writable.
     If you don't know how to do it, please ask for help from your web Administrator or Technical Support of your hosting company.   
</div>
<br><br>
<?php
}


function phpfmg_log_view(){
    $n = isset($_REQUEST['file'])  ? $_REQUEST['file']  : '';
    $files = array(
        1 => PHPFMG_EMAILS_LOGFILE,
        2 => PHPFMG_SAVE_FILE,
    );
    
    phpfmg_admin_header();
   
    $file = $files[$n];
    if( is_file($file) ){
        if( 1== $n ){
            echo "<pre>\n";
            echo join("",file($file) );
            echo "</pre>\n";
        }else{
            $man = new phpfmgDataManager();
            $man->displayRecords();
        };
     

    }else{
        echo "<b>No form data found.</b>";
    };
    phpfmg_admin_footer();
}


function phpfmg_log_download(){
    $n = isset($_REQUEST['file'])  ? $_REQUEST['file']  : '';
    $files = array(
        1 => PHPFMG_EMAILS_LOGFILE,
        2 => PHPFMG_SAVE_FILE,
    );

    $file = $files[$n];
    if( is_file($file) ){
        phpfmg_util_download( $file, PHPFMG_SAVE_FILE == $file ? 'form-data.csv' : 'email-traffics.txt', true, 1 ); // skip the first line
    }else{
        phpfmg_admin_header();
        echo "<b>No email traffic log found.</b>";
        phpfmg_admin_footer();
    };

}


function phpfmg_log_delete(){
    $n = isset($_REQUEST['file'])  ? $_REQUEST['file']  : '';
    $files = array(
        1 => PHPFMG_EMAILS_LOGFILE,
        2 => PHPFMG_SAVE_FILE,
    );
    phpfmg_admin_header();

    $file = $files[$n];
    if( is_file($file) ){
        echo unlink($file) ? "It has been deleted!" : "Failed to delete!" ;
    };
    phpfmg_admin_footer();
}


function phpfmg_util_download($file, $filename='', $toCSV = false, $skipN = 0 ){
    if (!is_file($file)) return false ;

    set_time_limit(0);


    $buffer = "";
    $i = 0 ;
    $fp = @fopen($file, 'rb');
    while( !feof($fp)) { 
        $i ++ ;
        $line = fgets($fp);
        if($i > $skipN){ // skip lines
            if( $toCSV ){ 
              $line = str_replace( chr(0x09), ',', $line );
              $buffer .= phpfmg_data2record( $line, false );
            }else{
                $buffer .= $line;
            };
        }; 
    }; 
    fclose ($fp);
  

    
    /*
        If the Content-Length is NOT THE SAME SIZE as the real conent output, Windows+IIS might be hung!!
    */
    $len = strlen($buffer);
    $filename = basename( '' == $filename ? $file : $filename );
    $file_extension = strtolower(substr(strrchr($filename,"."),1));

    switch( $file_extension ) {
        case "pdf": $ctype="application/pdf"; break;
        case "exe": $ctype="application/octet-stream"; break;
        case "zip": $ctype="application/zip"; break;
        case "doc": $ctype="application/msword"; break;
        case "xls": $ctype="application/vnd.ms-excel"; break;
        case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
        case "gif": $ctype="image/gif"; break;
        case "png": $ctype="image/png"; break;
        case "jpeg":
        case "jpg": $ctype="image/jpg"; break;
        case "mp3": $ctype="audio/mpeg"; break;
        case "wav": $ctype="audio/x-wav"; break;
        case "mpeg":
        case "mpg":
        case "mpe": $ctype="video/mpeg"; break;
        case "mov": $ctype="video/quicktime"; break;
        case "avi": $ctype="video/x-msvideo"; break;
        //The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
        case "php":
        case "htm":
        case "html": 
                $ctype="text/plain"; break;
        default: 
            $ctype="application/x-download";
    }
                                            

    //Begin writing headers
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public"); 
    header("Content-Description: File Transfer");
    //Use the switch-generated Content-Type
    header("Content-Type: $ctype");
    //Force the download
    header("Content-Disposition: attachment; filename=".$filename.";" );
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".$len);
    
    while (@ob_end_clean()); // no output buffering !
    flush();
    echo $buffer ;
    
    return true;
 
    
}
?>
