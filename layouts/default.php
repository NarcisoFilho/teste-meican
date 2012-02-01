<?php $args = $this->passedArgs ?>
<?php $base = Dispatcher::getInstance()->url(); ?>
<!doctype html>
<html class="no-js" lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?php echo Configure::read('systemName'); ?></title>
        
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <link rel="shortcut icon" href="<?php echo $base; ?>webroot/favicon.ico" type="image/x-icon" />
        <link rel="stylesheet" type="text/css" href="<?php echo $base; ?>webroot/css/meican3-theme/jquery-ui-1.8.16.custom.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $base; ?>webroot/css/style1.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $base; ?>webroot/css/timePicker.css" />
        <script type="text/javascript" src="<?php echo $base; ?>webroot/js/jquery.min.js"></script>
        <script type="text/javascript" src="<?php echo $base; ?>webroot/js/jquery-ui-1.8.16.custom.min.js"></script>
        <script type="text/javascript" src="<?php echo $base; ?>webroot/js/jquery.pjax.js"></script>
        <script type="text/javascript" src="<?php echo $base; ?>webroot/js/ui.spinner.js"></script>
        <?php
        //https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/themes/start/jquery-ui.css
        /* <script type="text/javascript" src="<?php echo $base; ?>webroot/js/jquery.form.js"></script>
          <script type="text/javascript" src="<?php echo $base; ?>apps/init/webroot/js/info_box.js"></script> */
        /*


          --> */
        ?>
        <?php if (Configure::read('dataTables')): ?>
            <script type="text/javascript" src="<?php echo $base; ?>webroot/js/jquery.dataTables.min.js"></script>
        <?php endif; ?>
        <script type ="text/javascript" src="<?php echo $base; ?>apps/circuits/webroot/js/googlemaps.js"></script>
        <script type ="text/javascript" src="<?php echo $base; ?>apps/circuits/webroot/js/StyledMarker.js"></script>
        <script type ="text/javascript" src="<?php echo $base; ?>webroot/js/main.js"></script>
        <script type ="text/javascript">
<?php // variavel para armazenar o ID quando a função setInterval() é usada
// cada vez que um link é carregado, é feito um clear na variável, para não carregar em páginas erradas        ?>
    var js_function_interval = null;
<?php // variavel global para armazenar o retorno de uma função de validação de um formulario, testada dentro do delegate        ?>
    var js_submit_form = true;
<?php //url base para geração de url, é o diretório onde o sistema está instalado no servidor         ?>
    var baseUrl = '<?php echo $this->url(''); ?>';
            
<?php // chamada para atualizar a hora   ?>
    $(document).ready(function() {
        setInterval("updateSystemTime()", 60000); 
    });
            
        </script>
        <?php if ($this->script->scriptArgs): ?>
            <script>
    <?php
    foreach ($this->script->scriptArgs as $name => $val) {
        echo "var $name = " . json_encode($val) . ";\n";
    }
    ?>
            </script>
        <?php endif; ?>
        <?php if ($this->script->jsFiles): ?>
            <?php
            foreach ($this->script->jsFiles as $f) {
                echo '<script type ="text/javascript" src="' . Dispatcher::getInstance()->url('') . $f . '"></script>';
            }
            ?>
        <?php endif; ?>	
        <?php
        if (!isset($scripts_for_layout))
            $scripts_for_layout = array();
        foreach ($scripts_for_layout as $script):
            ?>
            <script type="text/javascript" src="<?php echo Dispatcher::getInstance()->url('') . $script ?>"></script>
        <?php endforeach; ?>

        <?php /*  Coloca o theme roller
          <link type="text/css" rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.0/themes/base/jquery-ui.css" />
          <script>$(document).ready(function(){$('#switcher').themeswitcher();});</script>
          <script type="text/javascript" src="http://jqueryui.com/themeroller/themeswitchertool/"></script>
          <div id="switcher"></div> */ ?>

    </head>

    <body>
        <div class="fade-overlay" id="MainOverlay"> </div>
        <div id="left-panel">
            <div id="logo">
                <p>MEICAN</p>
            </div>
            <?php echo $this->element('menu', array('app' => 'init')); ?>
            <div id="system_date">
                <?php echo $this->element('time', array('app' => 'init')); ?>
            </div>
        </div>

        <div id="canvas">
            <?php echo $this->element('info_box', array('app' => 'init')); ?>
            <div id="workspace">
                <div id="main">
                    <?php echo $this->element('flash_box', array('app' => 'init') + compact('content_for_flash')); ?>
                    <?php echo $content_for_body; ?>
                </div>
                <?php echo $this->element('feedback', array('app' => 'init')); ?>
            </div>
            <div style="clear:both;"></div>
        </div>
        
<?php if ($analytics = Configure::read('analytics')): //'UA-28835796-1'?>   
<script>         
	var _gaq=[['_setAccount',<?php echo $analytics;?>],['_trackPageview']]; // Change UA-XXXXX-X to be your site's ID
	(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.async=1;
	g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
	s.parentNode.insertBefore(g,s)}(document,'script'));
</script>
<?php endif; ?>
<!--[if lt IE 7 ]>
	<script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.2/CFInstall.min.js"></script>
	<script>window.attachEvent("onload",function(){CFInstall.check({mode:"overlay"})})</script>
<![endif]-->

    </body>
</html>
