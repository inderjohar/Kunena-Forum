<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Site
 * @subpackage      Controller.User
 *
 * @copyright       Copyright (C) 2008 - 2020 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Filesystem\File;
use Joomla\Utilities\ArrayHelper;

/**
 * Class ComponentKunenaControllerUserItemDisplay
 *
 * @since  K4.0
 */
class ComponentKunenaControllerUserItemDisplay extends KunenaControllerDisplay
{
	/**
	 * @var KunenaUser
	 * @since   Kunena 6.0
	 */
	public $me;

	/**
	 * @var Joomla\CMS\User\User
	 * @since   Kunena 6.0
	 */
	public $user;

	/**
	 * @var KunenaUser
	 * @since   Kunena 6.0
	 */
	public $profile;

	/**
	 * @var
	 * @since   Kunena 6.0
	 */
	public $headerText;

	/**
	 * @var
	 * @since   Kunena 6.0
	 */
	public $tabs;

	/**
	 * @var string
	 * @since   Kunena 6.0
	 */
	protected $name = 'User/Item';

	/**
	 * Load user profile.
	 *
	 * @return  void
	 *
	 * @since   Kunena
	 * @throws  null
	 * @throws  Exception
	 */
	protected function before()
	{
		parent::before();

		// If profile integration is disabled, this view doesn't exist.
		$integration = KunenaFactory::getProfile();

		if (get_class($integration) == 'KunenaProfileNone')
		{
			throw new KunenaExceptionAuthorise(Text::_('COM_KUNENA_PROFILE_DISABLED'), 404);
		}

		$userid = $this->input->getInt('userid');

		require_once KPATH_SITE . '/models/user.php';
		$this->model = new KunenaModelUser(array(), $this->input);
		$this->model->initialize($this->getOptions(), $this->getOptions()->get('embedded', false));
		$this->state = $this->model->getState();

		$this->me      = KunenaUserHelper::getMyself();
		$this->user    = Factory::getUser($userid);
		$this->profile = KunenaUserHelper::get($userid);
		$this->profile->tryAuthorise('read');

		$activityIntegration = KunenaFactory::getActivityIntegration();
		$this->points        = $activityIntegration->getUserPoints($this->profile->userid);
		$this->medals        = $activityIntegration->getUserMedals($this->profile->userid);
		$this->private       = KunenaFactory::getPrivateMessaging();
		$socials             = $this->profile->socialButtons();
		$this->socials       = ArrayHelper::toObject($socials);

		$this->avatar  = $this->profile->getAvatarImage(KunenaFactory::getTemplate()->params->get('avatarType'), 'post');
		$this->banInfo = $this->config->showbannedreason
			? KunenaUserBan::getInstanceByUserid($this->profile->userid)
			: null;

		// Update profile hits.
		if (!$this->profile->exists() || !$this->profile->isMyself())
		{
			$this->profile->uhits++;
			$this->profile->save();
		}

		$Itemid = $this->input->getInt('Itemid');
		$format = $this->input->getCmd('format');

		if (!$Itemid && $format != 'feed' && KunenaConfig::getInstance()->sef_redirect)
		{
			$controller = BaseController::getInstance("kunena");

			if (KunenaConfig::getInstance()->profile_id)
			{
				$itemidfix = KunenaConfig::getInstance()->profile_id;
			}
			else
			{
				$menu      = $this->app->getMenu();
				$getid     = $menu->getItem(KunenaRoute::getItemID("index.php?option=com_kunena&view=user"));
				$itemidfix = $getid->id;
			}

			if (!$itemidfix)
			{
				$itemidfix = KunenaRoute::fixMissingItemID();
			}

			if (!$userid)
			{
				$controller->setRedirect(KunenaRoute::_("index.php?option=com_kunena&view=user&Itemid={$itemidfix}", false));
			}
			else
			{
				$controller->setRedirect(KunenaRoute::_("index.php?option=com_kunena&view=user&userid={$userid}&Itemid={$itemidfix}", false));
			}

			$controller->redirect();
		}

		$this->headerText = Text::sprintf('COM_KUNENA_VIEW_USER_DEFAULT', $this->profile->getName());
	}

	/**
	 * Prepare document.
	 *
	 * @return  void
	 * @since   Kunena
	 * @throws  Exception
	 */
	protected function prepareDocument()
	{
		$this->setMetaData('profile:username', $this->profile->getName(), 'property');

		if ($this->profile->getGender() == 1)
		{
			$this->setMetaData('profile:gender', Text::_('COM_KUNENA_MYPROFILE_GENDER_MALE'), 'property');
		}
		elseif ($this->profile->getGender() == 2)
		{
			$this->setMetaData('profile:gender', Text::_('COM_KUNENA_MYPROFILE_GENDER_FEMALE'), 'property');
		}
		else
		{
			$this->setMetaData('profile:gender', Text::_('COM_KUNENA_MYPROFILE_GENDER_UNKNOWN'), 'property');
		}

		$menu_item = $this->app->getMenu()->getActive();
		$config    = Factory::getConfig();
		$robots    = $config->get('robots');
		$image     = '';

		$this->setMetaData('og:url', Uri::current(), 'property');
		$this->setMetaData('og:type', 'profile', 'property');

		if (File::exists(JPATH_SITE . '/media/kunena/avatars/' . KunenaFactory::getUser($this->profile->id)->avatar))
		{
			$image = Uri::root() . 'media/kunena/avatars/' . KunenaFactory::getUser($this->profile->id)->avatar;
		}
		elseif ($this->profile->avatar == null || KunenaConfig::getInstance()->avatar_type && KunenaFactory::getUser($this->profile->id)->avatar == null)
		{
			if (File::exists(JPATH_SITE . '/' . KunenaConfig::getInstance()->emailheader))
			{
				$image = Uri::base() . KunenaConfig::getInstance()->emailheader;
			}
		}
		else
		{
			$image = $this->profile->getAvatarURL('Profile', '200');
		}

		$this->setMetaData('og:image', $image, 'property');

		if ($robots == '')
		{
			$this->setMetaData('robots', 'index, follow');
		}
		elseif ($robots == 'noindex, follow')
		{
			$this->setMetaData('robots', 'noindex, follow');
		}
		elseif ($robots == 'index, nofollow')
		{
			$this->setMetaData('robots', 'index, nofollow');
		}
		else
		{
			$this->setMetaData('robots', 'nofollow, noindex');
		}

		if ($menu_item)
		{
			$params             = $menu_item->getParams();
			$params_title       = $params->get('page_title');
			$params_keywords    = $params->get('menu-meta_keywords');
			$params_description = $params->get('menu-meta_description');
			$params_robots      = $params->get('robots');

			if (!empty($params_title))
			{
				$title = $params->get('page_title');
				$this->setTitle($title);
			}
			else
			{
				$title = Text::sprintf('COM_KUNENA_VIEW_USER_DEFAULT', $this->profile->getName());
				$this->setTitle($title);
			}

			$this->setMetaData('og:description', $title, 'property');
			$this->setMetaData('og:title', $this->profile->getName(), 'property');

			if (!empty($params_keywords))
			{
				$keywords = $params->get('menu-meta_keywords');
				$this->setKeywords($keywords);
			}
			else
			{
				$keywords = $this->config->board_title . ', ' . $this->profile->getName();
				$this->setKeywords($keywords);
			}

			if (!empty($params_description))
			{
				$description = $params->get('menu-meta_description');
				$this->setDescription($description);
			}
			else
			{
				$description = Text::sprintf('COM_KUNENA_META_PROFILE', $this->profile->getName(),
					$this->config->board_title, $this->profile->getName(), $this->config->board_title
				);
				$this->setDescription($description);
			}

			if (!empty($params_robots))
			{
				$robots = $params->get('robots');
				$this->setMetaData('robots', $robots);
			}
		}
	}
}
