<?php
/**
 * DTS - Generate all canvas and milestones required by the DTS process
 */
namespace leantime\domain\repositories {

    use leantime\domain\repositories;
    use leantime\domain\services;
    use pdo;

    class dts
    {

        /**
         * __construct - get database connection
         *
         * @access public
         */
        public function __construct()
        {
            $config = new core\config();
            $this->db = core\db::getInstance();

			// Array of canvas to create
        }

		/**
		 * setupProject - create default canvas and boars for project, if they do not yet exist
		 *
		 * @access public
		 * @param  int $projectId Project identifier
		 */
		public function setupProject(int $projectId): void
		{
		    // Identify owner of project as author
		    $authorId = $_SESSION['userdata']['id'];

			// Create canvas
			$canvasTitleAry = [ 'ea' => [ 'L1: Industry Environment', 
										  'L1: Industry Trends' ]
								'bm' => [ 'L1: Strategic Focus - Current',
										  'L1: Strategic Focus - Target',
										  'L1: Industry - Current',
										  'L1: Industry - Trends',
										  'L2: Business Model - Current',
										  'L2: Business Model - Target',
										  'L3: Business Model - Competitors' ]
								  ];
			foreach($canvasTitleAry as $canvas => $titleAry) {
				foreach($titleAry as $title) {
					$canvasRepoName ="\\leantime\\domain\\repositories\\".$canvas."canvas";
					$canvasRepo = new $canvasRepoName();
					if(!$canvasRepo->existCanvas($projectId, $title)) {
						$canvasRepo->addCanvas(['title' => $title, 'author' => $authorId, 'projectId' => $projectId]);
					}
				}
			}

			// Create project milestones
            $milestoneTitleAry = [ 'L1: Mandate', 'L1: Strategic Focus',
								   'L2: Detailed Business Model',
                                   'L3: Strategy' ];
            $ticketService = new services\tickets();
			foreach($milestoneTitleAry as $milestone => $title) {
				$params['headline'] = $title;
				$params['projectId'] = $projectId;
				$params['tags'] = "#ccc";
				$params['editFrom'] = date("Y-m-d");
                $params['editTo'] = date("Y-m-d", strtotime("+1 week"));
                $id = $ticketService->quickAddMilestone($params);
			}
			
		}
		
	}
}