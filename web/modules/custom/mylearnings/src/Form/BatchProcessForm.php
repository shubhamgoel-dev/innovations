<?php

namespace Drupal\mylearnings\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class BatchProcessForm extends ConfigFormBase {

    public function getFormId() {
        return 'mylearnings_batch_form';
    }

    protected function getEditableConfigNames() {
        return [
            'mylearnings.batch_op'
        ];
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('mylearnings.batch_op');

        $form['contenttype'] = [
          '#type' => 'select',
          '#name' => 'Content Type',
          '#description' => 'The Content type to alter',
          '#options' => [
            'article' => 'Article',
            'page' => 'Basic Page'
          ],
        //   '#default_value' => $config->get('contenttype')? $config->get('contenttype'):'article'
        ];

        return parent::buildForm($form, $form_state);
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $content_type = $form_state->getValue('contenttype');
        \Drupal::logger('abcd')->notice(print_r($content_type, true));
        $config = $this->config('mylearnings.batch_op');
        $config->set('contenttype', $content_type);
        $config->save();

        parent::submitForm($form, $form_state);

        $nids = \Drupal::entityQuery('node')
                ->condition('type', 'page')
                ->accessCheck(true)
                ->execute();
        $nodes = Node::loadMultiple($nids);

        foreach($nodes as $node) {
            $operations[] = [
                '\Drupal\mylearnings\Form\BatchProcessForm::updateNodes',
                [$node, 'Demo']
            ];
        }

        $batch = [
            'title' => t('Modifying Content'),
            'operations' => $operations,
            'init_message' => 'Learning..Batch Operations',
            'progress_message' => t('Processed @current out of @total.'),
            'finished' => '\Drupal\mylearnings\Form\BatchProcessForm::finished_updateNodes'
        ];

        batch_set($batch);
    }

    public static function updateNodes($node, $str, &$context) {
        try {
        $curTitle = $node->getTitle();
        $node->setTitle($str . ' ' . $curTitle);
        $node->save();

        $context['results'][] = $node->id() . ' ' . $node->label();
        $context['results']['success'] = $node->id();
        $context['message'] = 'Updating Node....' . $node->label();
        sleep(2);
        }
        catch(\Exception $e) {
            $context['results']['failure'] = 1;
        }
    }

    public static function finished_updateNodes($success, $results, $operations, $elapsed) {

        if($success && $results['success']) {
            $message = \Drupal::translation()->formatPlural(count($results), 'Uggh!!! One item processed.', '@count items processed so far.');
        }
        else {
          $message = t('Finished with an error.');
        }
        \Drupal::messenger()->addMessage($message);
    }
}