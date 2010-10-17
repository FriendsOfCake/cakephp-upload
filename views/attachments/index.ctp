<div class="attachments index">
	<h2><?php __('Attachments');?></h2>
	
	<ul class="actions">
		<li class='action_label add'>
						<?php echo $this->Html->link(__('Add', true), array('action' => 'add'), array('class' => 'add action_button', 'escape' => false,)); ?>
		</li>
		<li class='action_label reload'>
						<?php echo $this->Html->link(__('Reload', true), $this->here, array('class' => 'reload action_button', 'escape' => false,)); ?>
		
		</li>
	</ul>
	
	<div class='filter'>
		<?php
			echo $this->element(
				'behaviors',
				array(
					'plugin' => 'meta',
					'options' => array(
					'action' => 'index',
					'element' => 'filter',
				),
			)
		);
		?>
	</div>
	<div class="paging">
		<?php echo $this->Paginator->prev('<< ' . __('previous', true), array(), null, array('class'=>'disabled'));?>
	 | 	<?php echo $this->Paginator->numbers();?>
 |
		<?php echo $this->Paginator->next(__('next', true) . ' >>', array(), null, array('class' => 'disabled'));?>
	</div>
	<table class="manager">
	<tr>
							<th class="model"><?php echo $this->Paginator->sort('model');?></th>
					<th class="foreign_key"><?php echo $this->Paginator->sort('foreign_key');?></th>
					<th class="name"><?php echo $this->Paginator->sort('name');?></th>
							<th class="basename"><?php echo $this->Paginator->sort('basename');?></th>
					<th class="dir"><?php echo $this->Paginator->sort('dir');?></th>
					<th class="type"><?php echo $this->Paginator->sort('type');?></th>
					<th class="size"><?php echo $this->Paginator->sort('size');?></th>
					<th class="width"><?php echo $this->Paginator->sort('width');?></th>
					<th class="height"><?php echo $this->Paginator->sort('height');?></th>
					<th class="checksum"><?php echo $this->Paginator->sort('checksum');?></th>
					<th class="s3_key"><?php echo $this->Paginator->sort('s3_key');?></th>
					<th class="group"><?php echo $this->Paginator->sort('group');?></th>
					<th class="alternative"><?php echo $this->Paginator->sort('alternative');?></th>
							<?php
			echo $this->element(
					'behaviors',
					array(
						'plugin' => 'meta',
						'options' => array(
						'action' => 'index',
						'element' => 'th',
					),
				)
			);
			?>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($attachments as $attachment):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr class="<?php echo $class;?>">
		<td><?php echo $attachment['Attachment']['model']; ?>&nbsp;</td>
		<td><?php echo $attachment['Attachment']['foreign_key']; ?>&nbsp;</td>
		<td><?php echo $attachment['Attachment']['name']; ?>&nbsp;</td>
		<td><?php echo $attachment['Attachment']['basename']; ?>&nbsp;</td>
		<td><?php echo $attachment['Attachment']['dir']; ?>&nbsp;</td>
		<td><?php echo $attachment['Attachment']['type']; ?>&nbsp;</td>
		<td><?php echo $attachment['Attachment']['size']; ?>&nbsp;</td>
		<td><?php echo $attachment['Attachment']['width']; ?>&nbsp;</td>
		<td><?php echo $attachment['Attachment']['height']; ?>&nbsp;</td>
		<td><?php echo $attachment['Attachment']['checksum']; ?>&nbsp;</td>
		<td><?php echo $attachment['Attachment']['s3_key']; ?>&nbsp;</td>
		<td><?php echo $attachment['Attachment']['group']; ?>&nbsp;</td>
		<td><?php echo $attachment['Attachment']['alternative']; ?>&nbsp;</td>
		<?php
			echo $this->element(
					'behaviors',
					array(
						'plugin' => 'meta',
						'options' => array(
						'action' => 'index',
						'element' => 'td',
					),
				)
			);
		?>
		<td class="actions">
<div class='action_label view'>			<?php echo $this->Html->link(__('View', true), array('admin' => false, 'action' => 'view', (!empty($attachment['Attachment']['slug'])) ? $attachment['Attachment']['slug'] : $attachment['Attachment']['id'])); ?>
</div><div class='action_label edit'>			<?php echo $this->Html->link(__('Edit', true), array('action' => 'edit', $attachment['Attachment']['id'])); ?>
</div><div class='action_label delete'>			<?php echo $this->Html->link(__('Delete', true), array('action' => 'delete', $attachment['Attachment']['id']), null, sprintf(__('Are you sure you want to delete %s?', true), $attachment['Attachment']['name'])); ?>
</div>		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<ul class="actions">
		<li class='action_label new'><?php echo $this->Html->link(__('New', true), array('action' => 'add')); ?></li>
	</ul>
	
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
	));
	?>	</p>

	<div class="paging">
		<?php echo $this->Paginator->prev('<< ' . __('previous', true), array(), null, array('class'=>'disabled'));?>
	 | 	<?php echo $this->Paginator->numbers();?>
 |
		<?php echo $this->Paginator->next(__('next', true) . ' >>', array(), null, array('class' => 'disabled'));?>
	</div>
</div>