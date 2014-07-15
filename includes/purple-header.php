<!DOCTYPE HTML>
<html lang="en">
<head>
<title>Ivy on the park</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <!-- Bootstrap core JavaScript -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="includes/js/bootstrap.min.js"></script>
<!-- COLORBOX -->
<link rel="stylesheet" href="includes/css/colorbox.css">
<script src="includes/js/jquery.colorbox-min.js"></script>
<script>
      $(document).ready(function(){
        $(".group3").colorbox({rel:'group3', transition:"elastic", width:"90%", height:"90%"});
        $(".group4").colorbox({rel:'group4', slideshow:true});
        $(".callbacks").colorbox({
          onOpen:function(){ alert('onOpen: colorbox is about to open'); },
          onLoad:function(){ alert('onLoad: colorbox has started to load the targeted content'); },
          onComplete:function(){ alert('onComplete: colorbox has displayed the loaded content'); },
          onCleanup:function(){ alert('onCleanup: colorbox has begun the close process'); },
          onClosed:function(){ alert('onClosed: colorbox has completely closed'); }
        });

        $('.non-retina').colorbox({rel:'group5', transition:'none'})
        $('.retina').colorbox({rel:'group5', transition:'none', retinaImage:true, retinaUrl:true});
        
        $("#click").click(function(){ 
          $('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
          return false;
        });
      });
    </script>
<script src="./includes/js/modernizr.js"></script>
<script defer src="includes/js/jquery.flexslider.js"></script>
<script type="text/javascript">
    $(function(){
      SyntaxHighlighter.all();
    });
    $(window).load(function(){
      $('.flexslider').flexslider({
        animation: "slide",
        start: function(slider){
          $('body').removeClass('loading');
        }
      });
    });
</script>
<link rel="stylesheet" type="text/css" href="includes/css/style.css" media="screen">
<link rel="stylesheet" href="includes/css/flexslider.css" type="text/css" media="screen" />
</head>
<body>