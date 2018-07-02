<?php
/**
 * Kunena Component
 * @package         Kunena.Template.Crypsis
 * @subpackage      Layout.User
 *
 * @copyright       Copyright (C) 2008 - 2018 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

$this->addStyleSheet('assets/css/bootstrap.datepicker.css');
$this->addScript('assets/js/bootstrap.datepicker.js');
$this->addScript('assets/js/profile.js');
?>
<h3>
	<?php echo $this->headerText; ?>
</h3>

<table class="table table-bordered table-striped table-hover">
	<tbody>
	<?php if ($this->config->personal) : ?>
		<tr>
			<td class="col-md-3">
				<label for="personaltext">
					<?php echo JText::_('COM_KUNENA_MYPROFILE_PERSONALTEXT'); ?>
				</label>
			</td>
			<td>
				<input class="form-control hasTooltip" id="personaltext" type="text"
				       maxlength="<?php echo (int) $this->config->maxpersotext; ?>"
				       name="personaltext" value="<?php echo $this->escape($this->profile->personalText); ?>"
				       title="<?php echo JText::_('COM_KUNENA_MYPROFILE_PERSONALTEXT_DESC') ?>"/>
			</td>
		</tr>
	<?php endif; ?>
	<tr>
		<td>
			<label for="birthdate">
				<?php echo JText::_('COM_KUNENA_MYPROFILE_BIRTHDATE'); ?>
			</label>
		</td>
		<td>
			<div id="birthdate">
				<div class="input-group date">
					<input class="form-control hasTooltip" type="text" name="birthdate" data-date-format="mm/dd/yyyy"
					       value="<?php echo $this->profile->birthdate == '0001-01-01' ? Factory::getDate()->format('mm/dd/yyyy') : KunenaDate::getInstance($this->profile->birthdate)->format('m/d/Y'); ?>"
					       title="<?php echo JText::_('COM_KUNENA_MYPROFILE_BIRTHDATE_DESC') ?>">
					<span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<label for="location">
				<?php echo JText::_('COM_KUNENA_MYPROFILE_LOCATION'); ?>
			</label>
		</td>
		<td>
			<input id="location" type="text" name="location" class="form-control hasTooltip"
			       value="<?php echo $this->escape($this->profile->location); ?>"
			       title="<?php echo JText::_('COM_KUNENA_MYPROFILE_LOCATION_DESC') ?>"/>
		</td>
	</tr>
	<tr>
		<td>
			<label for="gender">
				<?php echo JText::_('COM_KUNENA_MYPROFILE_GENDER'); ?>
			</label>
		</td>
		<td>
			<?php
			// Make the select list for the view type
			$gender[] = HTMLHelper::_('select.option', 0, JText::_('COM_KUNENA_MYPROFILE_GENDER_UNKNOWN'));
			$gender[] = HTMLHelper::_('select.option', 1, JText::_('COM_KUNENA_MYPROFILE_GENDER_MALE'));
			$gender[] = HTMLHelper::_('select.option', 2, JText::_('COM_KUNENA_MYPROFILE_GENDER_FEMALE'));

			// Build the html select list
			echo HTMLHelper::_(
				'select.genericlist', $gender, 'gender', 'class="inputbox form-control hasTooltip" title="' . JText::_('COM_KUNENA_MYPROFILE_GENDER') . '" size="1"', 'value', 'text',
				$this->escape($this->profile->gender), 'gender'
			);
			?>
		</td>
	</tr>
	<tr>
		<td>
			<label for="social-site">
				<?php echo JText::_('COM_KUNENA_MYPROFILE_WEBSITE_NAME'); ?>
			</label>
		</td>
		<td>
				<span class="hasTooltip" title="<?php echo JText::_('COM_KUNENA_MYPROFILE_WEBSITE_NAME')
					. '::' . JText::_('COM_KUNENA_MYPROFILE_WEBSITE_NAME_DESC'); ?>">
					<input id="social-site" type="text" maxlength="25" name="websitename" class="form-control"
					       value="<?php echo $this->escape($this->profile->websitename); ?>"/>
				</span>
		</td>
	</tr>
	<tr>
		<td>
			<label for="social-url">
				<?php echo JText::_('COM_KUNENA_MYPROFILE_WEBSITE_URL'); ?>
			</label>
		</td>
		<td>
				<span class="hasTooltip"
				      title="<?php echo JText::_('COM_KUNENA_MYPROFILE_WEBSITE_URL') . '::' . JText::_('COM_KUNENA_MYPROFILE_WEBSITE_URL_DESC'); ?>">
					<input id="social-url" type="url" name="websiteurl" class="form-control"
					       value="<?php echo $this->escape($this->profile->getWebsiteURL()); ?>"/>
				</span>
		</td>
	</tr>

	<?php if ($this->config->social) : ?>
		<?php foreach ($this->social as $key => $social) : ?>
			<tr>
				<td>
					<label for="social-<?php echo $key; ?>">
						<?php echo JText::_('COM_KUNENA_MYPROFILE_' . $key); ?>
					</label>
				</td>
				<td>
					<?php if ($social != 'qq') : ?>
					<span class="hasTooltip" title="<?php echo JText::_("COM_KUNENA_MYPROFILE_{$key}")
						. '::' . JText::_("COM_KUNENA_MYPROFILE_{$key}_DESC"); ?>">
				<?php endif; ?>
						<input id="social-<?php echo $key; ?>" type="text" name="<?php echo $key ?>"
						       value="<?php echo $this->escape($this->profile->$key); ?>"/>
				</span>
				</td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ($this->config->signature) : ?>
		<tr>
			<td>
				<label for="signature">
					<?php echo JText::_('COM_KUNENA_MYPROFILE_SIGNATURE'); ?>
				</label>
			</td>
			<td>
			<span class="hasTooltip" title="<?php echo JText::_('COM_KUNENA_MYPROFILE_SIGNATURE')
				. '::' . JText::_('COM_KUNENA_MYPROFILE_SIGNATURE_DESC'); ?>">
				<textarea class="input-xxlarge form-control" maxlength="<?php echo (int) $this->config->maxsig; ?>"
				          name="signature" id="signature" rows="10"
				          cols="30"><?php echo $this->escape($this->profile->signature); ?></textarea>
			</span>
			</td>
		</tr>
	<?php endif; ?>
	</tbody>
</table>
