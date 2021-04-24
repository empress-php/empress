<?php

use Amp\Http\Server\FormParser\Form;
use Amp\Loop;
use Amp\Promise;
use Empress\Application;
use Empress\Context;
use Empress\Empress;
use Empress\Routing\RouteCollector\AnnotatedRouteCollectorTrait;
use Empress\Routing\RouteCollector\Attribute\Group;
use Empress\Routing\RouteCollector\Attribute\Route;
use Empress\Routing\RouteCollector\RouteCollectorInterface;
use Empress\Validation\Validator\ValidatorException;
use Empress\Validation\Validator\ValidatorInterface;
use function Amp\call;

require __DIR__ . '/../vendor/autoload.php';


class Person
{
    public function __construct(
        private string $name,
        private int $age
    ) {
    }
}

#[Group('/validate')]
class ValidationController implements RouteCollectorInterface
{
    use AnnotatedRouteCollectorTrait;

    #[Route('GET', '/person')]
    public function index(Context $ctx)
    {
        $person = $ctx['person']->to(Person::class)->unwrapOrNull();

        $ctx->html('Hello World!');
    }
}

$app = Application::create(9010);
$app->routes(new ValidationController());

$registry = $app->getValidatorRegistry();
$registry->register(Person::class, new class implements ValidatorInterface {

    /**
     * @param mixed $form
     * @return Promise<Person>
     */
    public function validate(mixed $form): Promise
    {
        return call(function () use ($form) {
            if (!$form instanceof Form) {
                throw new ValidatorException('Expected an instance of class ' . Form::class);
            }

            $name = $form->getValue('name');
            $age = $form->getValue('age');
        });
    }
});

$empress = new Empress($app);

Loop::run([$empress, 'boot']);
