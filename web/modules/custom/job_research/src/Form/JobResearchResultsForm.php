<?php

namespace Drupal\job_research\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\job_research\Service\JobResearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class JobResearchResultsForm
 */
class JobResearchResultsForm extends FormBase {
    
    /**
     * @var Drupal\job_research\Service\JobResearchService
     */
    protected $jobResearchService;

    /**
     * @param \Drupal\job_research\Service\JobResearchService
     */
    public function __construct(JobResearchService $jobResearchService) {
        $this->jobResearchService = $jobResearchService;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static (
            $container->get('job_research.utility'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'job_research_filter_form_results';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        
        $tableRows = [];
        $config = \Drupal::service('config.factory')->getEditable('job.research');
        $filterValues = $config->get('jobfilters');
        $researchResults = $this->jobResearchService->getJobsFromDatabase($filterValues);
        $tableHeader = [
            'sr_no' => $this->t("Sr. No"),
            'pos' => $this->t("Position"),
            'loc' => $this->t("Location"),
            'exp' => $this->t("Experience"),
            'webpage' => $this->t("Webpage"),
        ];
    
        if (!empty($researchResults)) {
            foreach ($researchResults as $key => $mid) {
                $tableRows[$mid['id']] = [
                'sr_no' => $key + 1,
                'pos' => $mid['position'],
                'loc' => $mid['location'],
                'exp' => $mid['experience'],
                'webpage' => $mid['webpage']
                ];
            }
        }
      
        $form['mid'] = [
            '#type' => 'tableselect',
            '#title' => $this->t('Filtered Job List'),
            '#header' => $tableHeader,
            '#options' => $tableRows,
            '#empty' => $this->t("No Jobs available for your provided Filters"),
        ];
      
        $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Get More Details'),
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
        $values = array_filter($form_state->getValues()['mid']);
        $full_job = $this->jobResearchService->extractJobs($values);

        \Drupal::logger('myjob')->notice(print_r($full_job, true));
    }
}