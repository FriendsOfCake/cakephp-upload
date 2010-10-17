<div class="attachments add">
<p class='breadcrumbs'>		<?php echo $this->Html->link(__('List', true).' Attachments', array('action' => 'index'), array('class' => 'list', 'escape' => false,)); ?> 
 » Add <?php  __('Attachment');?></p>
		<ul class="actions">
				<li class='action_label list'><?php echo $this->Html->link(__('List', true), array('action' => 'index'));?></li>
			</ul>
<?php echo $this->Form->create('Attachment', array('class' => 'generic', 'type' => 'file',));?>
	<fieldset>
 		<legend><?php __('Add Attachment'); ?></legend>
	<?php
		echo $this->Form->input('model');
		echo $this->Form->input('foreign_key');
		echo $this->Form->input('name');
		echo $this->Form->input('basename');
		echo $this->Form->input('dir');
		echo $this->Form->input('type');
		echo $this->Form->input('size');
		echo $this->Form->input('width');
		echo $this->Form->input('height');
		echo $this->Form->input('checksum');
		echo $this->Form->input('s3_key');
		echo $this->Form->input('group');
		echo $this->Form->input('alternative');
	?>

	<?php foreach ($params_named as $key => $named): ?>
		<?php echo $form->hidden('Named.'.$key, array('value' => $named,)); ?>
	<?php endforeach ?>
	</fieldset>

	<?php
	echo $this->element(
		'behaviors',
		array(
			'plugin' => 'meta',
			'options' => array(
				'action' => 'edit',
			),
		)
	);
	?>
<?php echo $this->Form->end(__('Submit', true));?>
</div>