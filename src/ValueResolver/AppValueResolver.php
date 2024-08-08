<?php
// src/ValueResolver/AppValueResolver.php
namespace App\ValueResolver;

use App\Entity\Application;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Uid\Uuid;

readonly class AppValueResolver implements ValueResolverInterface
{

    public function __construct(private EntityManagerInterface $entityManager, private SluggerInterface $slugger)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (Application::class !== $argument->getType()) {
            return [];
        }


        /**
         * @var ApplicationRepository $appRepository
         */
        $appRepository = $this->entityManager->getRepository(Application::class);


        $application = $appRepository->findApplication();
        if (is_null($application)) {
            $application = new Application();
            $application->setName('Default Application');
            $application->setSlug($this->slugger->slug($application->getName()));
            $application->setUuid(Uuid::v4());

            $this->entityManager->persist($application);

            $this->entityManager->flush();

//
//            throw new NotFoundHttpException('Application not found');
        }


        return [$application];
    }
}
