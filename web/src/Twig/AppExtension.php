<?php

namespace App\Twig;

use App\Entity\Report;
use App\Entity\User;
use App\Entity\UserNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Inflector\Inflector;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class AppExtension.
 */
class AppExtension extends AbstractExtension
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        RouterInterface $router
    ) {
        $this->em = $em;
        $this->translator = $translator;
        $this->router = $router;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_entity', [$this, 'getEntityFunction']),
            new TwigFunction('get_entity_route', [$this, 'getEntityRouteFunction']),
            new TwigFunction('get_notification_information', [$this, 'getNotificationInformationFunction']),
            new TwigFunction('get_countries', [$this, 'getCountriesFunction']),
            new TwigFunction('get_user_avatar_url', [$this, 'getUserAvatarUrlFunction']),
            new TwigFunction('dump_data', [$this, 'dumpDataFunction']),
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('singularize', [$this, 'singularizeFilter']),
            new TwigFilter('only_with_values', [$this, 'onlyWithValuesFilter']),
        ];
    }

    /**
     * @return object
     */
    public function getEntityFunction(string $entityType, int $entityId)
    {
        $entityClass = Report::getEntityClass($entityType);

        return $this->em->getRepository($entityClass)->findOneById($entityId);
    }

    /**
     * @return string
     */
    public function getEntityRouteFunction(string $entityType)
    {
        return Report::getEntityRoute($entityType);
    }

    /**
     * @return array
     */
    public function getNotificationInformationFunction(UserNotification $userNotification)
    {
        $type = $userNotification->getType();
        $user = $userNotification->getUser();
        $data = $userNotification->getData();

        $link = '#';
        $textParams = [];

        if (in_array($type, [
            UserNotification::TYPE_USER_FOLLOW,
            UserNotification::TYPE_USER_FOLLOW_REQUEST,
        ])) {
            $userFollower = $this->em
                ->getRepository(User::class)
                ->findOneById($data['user_id']);
            $textParams['%user_username%'] = $userFollower
                ? '<b>' . $userFollower->getUsername() . '</b>'
                : $this->translator->trans('deleted user');
        }

        if (UserNotification::TYPE_USER_FOLLOW === $type) {
            $link = $this->router->generate('users.followers', [
                'username' => $user->getUsername(),
            ]);
        } elseif (UserNotification::TYPE_USER_FOLLOW_REQUEST === $type) {
            $link = $this->router->generate('users.follower_requests');
        }

        $transKey = 'text.' . $type;

        return [
            'link' => $link,
            'text' => $this->translator->trans(
                $transKey,
                $textParams,
                'notifications'
            ),
        ];
    }

    /**
     * @return array
     */
    public function getCountriesFunction($locale = null)
    {
        \Locale::setDefault($locale);

        return Countries::getNames();
    }

    /**
     * @return string
     */
    public function getUserAvatarUrlFunction(User $user)
    {
        return $this->router->generate('home', [], UrlGeneratorInterface::ABSOLUTE_URL) .
            'assets/images/avatars/' .
            $user->getAvatarImage();
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    public function dumpDataFunction($data)
    {
        $html = '';

        if (is_object($data)) {
            $data = (array) $data;
        }

        if (
            !empty($data) &&
            is_array($data)
        ) {
            $html .= '<ul>';
            foreach ($data as $key => $val) {
                $html .= '<li>';
                $html .= '<b>' . $key . '</b>: ';
                if (is_array($val)) {
                    $html .= $this->dumpDataFunction($val);
                } else {
                    $html .= null === $val
                        ? '<i>null</i>'
                        : $val;
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
        } elseif (!empty($data)) {
            $html .= $data;
        } elseif (null === $data) {
            $html .= '<i>null</i>';
        } elseif ($data === []) {
            $html .= '<i>[]</i>';
        }

        return $html;
    }

    /**
     * @return string
     */
    public function singularizeFilter(string $string)
    {
        return Inflector::singularize($string);
    }

    /**
     * @return array
     */
    public function onlyWithValuesFilter(array $array)
    {
        foreach ($array as $key => $value) {
            if (!$value) {
                unset($array[$key]);
            }
        }

        return $array;
    }
}
