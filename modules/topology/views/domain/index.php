<?php 
/**
 * @copyright Copyright (c) 2012-2016 RNP
 * @license http://github.com/ufrgs-hyman/meican2#license
 */

use yii\grid\GridView;
use yii\widgets\ActiveForm;

use meican\base\grid\IcheckboxColumn;
use meican\base\widgets\GridButtons;

use yii\helpers\Html;
use yii\helpers\Url;

use meican\topology\assets\domain\IndexAsset;

IndexAsset::register($this);

$this->params['header'] = ["Domains", ['Home', 'Topology']];

?>

<div class="box box-default">
    <div class="box-header with-border">
        <?= GridButtons::widget(); ?>
    </div>
    <div class="box-body">
        <?php

        $form = ActiveForm::begin([
            'method' => 'post',
            'action' => ['delete'],
            'id' => 'domain-form',
            'enableClientScript'=>false,
            'enableClientValidation' => false,
        ]);
        
        echo GridView::widget([
            'dataProvider' => $domains,
            'id' => 'grid',
            'layout' => "{items}{summary}{pager}",
            'columns' => array(
                array(
                    'class'=>IcheckboxColumn::className(),
                    'name'=>'delete',         
                    'multiple'=>false,
                    'headerOptions'=>['style'=>'width: 2%;'],
                ),
                [
            		'format' => 'html',
            		'value' => function($dom){ 
	            		$href = Url::toRoute(['/topology/domain/update', 'id'=>$dom->id]);
	            		return Html::a(Html::tag('span', '', ['class' => 'fa fa-pencil']), $href);
            		},
            		'headerOptions'=>['style'=>'width: 2%;'],
            	],
                [
                    'label' => Yii::t('topology', 'Name'),
                    'value' => 'name',
                    'headerOptions'=>['style'=>'width: 50%;'],
                ],
                [
                    'label' => Yii::t('topology', 'Default Policy'),
                    'value' => function($dom){
                        return $dom->getPolicy();
                    },
                    'headerOptions'=>['style'=>'width: 46%;'],
                ],
            ),
        ]); 

        ActiveForm::end();

        ?>
    </div>
</div>
