<?php

namespace Drupal\job_research\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * 
 * JobRearch Service.
 */
class JobResearchService {

    /**
     * @var Drupal\Core\Database\Connection
     */
    protected $connection;

    /**
     * @var Drupal\Core\Entity\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param Drupal\Core\Database\Connection $connection
     * A connection argument
     * 
     * @param Drupal\Core\Entity\EntityManagerInterface $entityManager
     * A enitityType Manager argument
     * 
     */
    public function __construct(Connection $connection, EntityTypeManagerInterface $entityManagerInterface) {
        $this->connection = $connection;
        $this->entityManager = $entityManagerInterface;
    }

    /**
     * 
     * This function returns the filtered jobs from database.
     */
    public function getJobsFromDatabase($jobFilterValues) {
        
        $query = $this->connection->select('job_research', 'jr')
                ->fields('jr', ['id', 'position', 'location', 'posting_period', 'experience', 'work', 'webpage' ])
                ->condition('jr.position', $jobFilterValues['position']);
                // ->condition('jr.location', $jobFilterValues['location'])
                // ->condition('jr.posting_period', $jobFilterValues['posting_period'])
                // ->condition('jr.experience', $jobFilterValues['experience'])
                // ->condition('jr.work', $jobFilterValues['work_type']);
        $filteredJobs = $query->execute();
        
        $filteredJobArr = [];
        foreach($filteredJobs as $filteredJob) { 
            $filteredJobArr[] = [
                'id' => $filteredJob->id,
                'position' => $filteredJob->position,
                'location' => $filteredJob->location,
                'posting_period' => $filteredJob->posting_period,
                'experience' => $filteredJob->experience,
                'work' => $filteredJob->work,
                'webpage' => $filteredJob->webpage
            ];
        }

        return $filteredJobArr;
    }

    public function extractJobs($mids) {
        $output="";
        foreach($mids as $mid) {
            $query = $this->connection->select('job_research', 'jr')
                    ->fields('jr', ['id', 'webpage' ])
                    ->condition('jr.id', $mid);

            $jobLink = $query->execute();
            foreach($jobLink as $jl) {
                $output = shell_exec("python3 joblinkscraper.py $jl->webpage");
            print_r($output);
                // $output = shell_exec("python3 ../scripts/test.py");
            // \Drupal::logger('dsgf')->notice(print_r($output, true));
            \Drupal::logger('dsgf')->notice(print_r("python3 ./joblinkscraper.py $jl->webpage", true));
            }
        }

        return $output;
        //   $output = shell_exec('python3 "../scripts/test.py"');
        // $output = shell_exec("python3 scripts/main.py $position $location $exp $posting_period $work");
        
    }
}