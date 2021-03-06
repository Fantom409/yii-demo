<?php

declare(strict_types=1);

/**
 * @var $csrf string
 * @var $form Yiisoft\Form\FormModel
 * @var $url \Yiisoft\Router\UrlGeneratorInterface
 * @var $field \Yiisoft\Form\Widget\Field
 */

use Yiisoft\Form\Widget\Form;
use Yiisoft\Html\Html;
use Yiisoft\Yii\Bootstrap4\Alert;

if (isset($sent)) {
    echo Alert::widget()
              ->options(['class' => !$form->hasErrors() ? 'alert-success' : 'alert-danger'])
              ->body(
                  $sent && !$form->hasErrors()
                      ? 'Thanks to contact us, we\'ll get in touch with you as soon as possible.'
                      : 'Some values is incorrect'
              );
}
?>

<div>

    <?= Form::begin()
        ->action($url->generate('site/contact'))
        ->options(
            [
                'id' => 'form-contact',
                'csrf' => $csrf,
                'enctype' => 'multipart/form-data',
            ]
        )
        ->start() ?>

    <?= $field->config($form, 'username') ?>
    <?= $field->config($form, 'email')->input('email') ?>
    <?= $field->config($form, 'subject') ?>
    <?= $field->config($form, 'body')
        ->textArea(['class' => 'form-control textarea', 'rows' => 2]) ?>
    <?= $field->config($form, 'attachFiles')
        ->inputCssClass('file-input')
        ->fileInput(
            ['type' => 'file', 'multiple' => 'multiple', 'name' => 'attachFiles[]'],
            true,
        ) ?>

    <?= Html::submitButton(
            'Submit',
            [
            'class' => 'btn btn-primary'
        ]
        ) ?>

    <?= Form::end() ?>

</div>
