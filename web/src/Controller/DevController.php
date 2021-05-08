<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DevController.
 */
class DevController extends AbstractController
{
    /**
     * @Route("/dev", name="dev")
     */
    public function index(Request $request, KernelInterface $kernel)
    {
        $environment = $kernel->getEnvironment();
        if ($environment !== 'dev') {
            throw $this->createAccessDeniedException();
        }

        $action = $request->query->get('action');

        $application = new Application($kernel);
        $application->setAutoExit(false);

        if ($action === 'database_recreate') {
            $input = new ArrayInput([
                'command' => 'app:database:recreate',
            ]);
        } else {
            throw $this->createNotFoundException();
        }

        $output = new BufferedOutput();

        $application->run($input, $output);

        $content = $output->fetch();

        return new Response($content);
    }
}
