<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_places
 *
 * @copyright   Copyright (C) 2015 Saity74 LLC, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$app				= JFactory::getApplication();
$user				= JFactory::getUser();
$userId			= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn		= $this->escape($this->state->get('list.direction'));
$archived		= $this->state->get('filter.published') == 2 ? true : false;
$trashed		= $this->state->get('filter.published') == -2 ? true : false;
$saveOrder	= $listOrder == 'p.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_places&task=point.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'pointsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$assoc = JLanguageAssociations::isEnabled();
?>

<form action="<?php echo JRoute::_('index.php?option=com_places&view=points'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="pointsList">
				<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', '', 'p.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%" class="center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" style="min-width:55px" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'p.state', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'p.title', $listDirn, $listOrder); ?>
						</th>
						<th width="5%">
							<?php echo JHtml::_('searchtools.sort', 'COM_PLACES_HEADING_TOWN', 'p.townid', $listDirn, $listOrder); ?>
						</th>
						<th width="30%">
							<?php echo JHtml::_('searchtools.sort', 'COM_PLACES_HEADING_ADDRESS', 'p.address', $listDirn, $listOrder); ?>
						</th>
					<?php if ($assoc) : ?>
						<th width="5%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_CONTENT_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
						</th>
					<?php endif;?>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort',  'JAUTHOR', 'p.created_by', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JDATE', 'p.created', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'p.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$item->max_ordering = 0;
					$ordering   = ($listOrder == 'p.ordering');
					//$canCreate  = $user->authorise('core.create',     'com_places.region.' . $item->regionid);
					$canEdit    = $user->authorise('core.edit',       'com_places.point.' . $item->id);
					$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
					$canEditOwn = $user->authorise('core.edit.own',   'com_places.point.' . $item->id);
					$canChange  = $user->authorise('core.edit.state', 'com_places.point.' . $item->id) && $canCheckin;
					?>
					<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->townid; ?>">
						<td class="order nowrap center hidden-phone">
							<?php 
							$iconClass = '';
							if (!$canChange)
							{
								$iconClass = ' inactive';
							}
							elseif (!$saveOrder)
							{
								$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
							}
							?>
							<span class="sortable-handler<?php echo $iconClass ?>">
								<i class="icon-menu"></i>
							</span>
							<?php if ($canChange && $saveOrder) : ?>
								<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
							<?php endif; ?>
						</td>
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="center">
							<div class="btn-group">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'points.', $canChange, 'cb'); ?>
								<?php //TODO: echo JHtml::_('placesadministrator.featured', $item->featured, $i, $canChange); ?>
								<?php
										// Create dropdown items
									$action = $archived ? 'unarchive' : 'archive';
									JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'points');

									$action = $trashed ? 'untrash' : 'trash';
									JHtml::_('actionsdropdown.' . $action, 'cb' . $i, 'points');

									// Render dropdown list
									echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
								?>
							</div>
						</td>
						<td class="has-context">
							<div class="pull-left break-word">
								<?php if ($item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'points.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($item->language == '*'):?>
									<?php $language = JText::alt('JALL', 'language'); ?>
								<?php else:?>
									<?php $language = $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
								<?php endif;?>
								<?php if ($canEdit || $canEditOwn) : ?>
									<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_places&task=point.edit&id='.(int) $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
										<?php echo $item->lat . ' ' . $item->lng; ?>
									</a>
								<?php else : ?>
									<?php echo $item->lat . ' ' . $item->lng; ?>
								<?php endif; ?>
								<div class="small">
									<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
									<span class="small break-word">
										<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
									</span>
								</div>
							</div>
						</td>
						<td>
							<?php echo $this->escape($item->town_title); ?>
						</td>
						<td>
							<?php echo $this->escape($item->address); ?>
						</td>
						<?php if ($assoc) : ?>
						<td class="hidden-phone">
							<?php if ($item->association) : ?>
								<?php echo JHtml::_('placesadministrator.association', $item->id); ?>
							<?php endif; ?>
						</td>
						<?php endif;?>
						<td class="small hidden-phone">
							<?php if ($item->created_by_alias) : ?>
								<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
								<?php echo $this->escape($item->author_name); ?></a>
								<p class="smallsub"> <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->created_by_alias)); ?></p>
							<?php else : ?>
								<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
								<?php echo $this->escape($item->author_name); ?></a>
							<?php endif; ?>
						</td>
						<td class="nowrap small hidden-phone">
							<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
						</td>
						<td class="center hidden-phone">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		
		<?php echo $this->pagination->getListFooter(); ?>
		<?php // Load the batch processing form. ?>
		<?php echo $this->loadTemplate('batch'); ?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
