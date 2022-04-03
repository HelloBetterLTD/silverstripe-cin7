<?php


namespace SilverStripers\Cin7\Dev;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripers\Cin7\Connector\Cin7Connector;
use SilverStripers\Cin7\Connector\Loader\BranchLoader;

class ImportBranches extends BuildTask
{

    private static $segment = 'cin7-branch-import';

    protected $title = 'Cin7:Import Branches';

    protected $description = 'Import Branches';

    public function run($request)
    {
        $conn = Cin7Connector::init();
        $branches = $conn->getBranches();

        /* @var $loader BranchLoader */
        $loader = Injector::inst()->get(BranchLoader::class);
        foreach ($branches as $branch) {
            $loader->load($branch);
        }
    }

}
