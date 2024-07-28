<?php

namespace Drupal\job_research\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\job_research\Service\JobResearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
/**
 * Class JobResearchFilterForm
 */
class JobResearchFilterForm extends FormBase {
    
    /**
     * @var Drupal\job_research\Service\JobResearchService
     */
    protected $jobResearchService;

    /**
     * @param \Drupal\job_research\Service\JobResearchService
     * @param \Drupal\Core\Config\ConfigFactoryInterface
     */
    public function __construct(JobResearchService $jobResearchService) {
        $this->jobResearchService = $jobResearchService;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static (
            $container->get('job_research.utility')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'job_research_filter_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['position'] = [
            '#type' => 'textfield',
            '#title' => 'Enter the position you are looking for: ',
            '#required' => true
        ];

        $form['location'] = [
            '#type' => 'textfield',
            '#title' => 'Enter the location (or "remote"): ',
            '#required' => true
        ];

        $form['experience'] = [
            '#type' => 'radios',
            '#title' => 'Level of experience:',
            '#options' => [
                '1' => 'Trainee',
                '2' => 'Assistant',
                '3' => 'Junior',
                '4' => 'Senior'
            ],
            '#required' => true
        ];

        $form['posting_period'] = [
            '#type' => 'radios',
            '#title' => 'Posting Period:',
            '#options' => [
                'r86400' => 'Last 24 hours',
                'r604800' => 'Last week',
                'r2592000' => 'Last month',
            ],
            '#required' => true
        ];
        
        $form['work_type'] = [
            '#type' => 'radios',
            '#title' => 'Type of Work:',
            '#options' => [
                '1' => 'On-Site',
                '2' => 'Remote',
                '3' => 'Hybrid',
            ],
            '#required' => true
        ];

        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => 'Submit'
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        // Get all the form values, by user
        $formValues = $form_state->getValues();
        $position = $formValues['position'];
        $location = $formValues['location'];
        $experience = $formValues['experience'];
        $posting_period = $formValues['posting_period'];
        $work_type = $formValues['work_type'];

        // $form = \Drupal::formBuilder()->getForm('Drupal\job_research\Form\JobResearchResultsForm', $formValues);
        $config = \Drupal::service('config.factory')->getEditable('job.research');
        $config->set('jobfilters', $formValues)->save();
        $form_state->setRedirect('job_research.form_results');
    }
}