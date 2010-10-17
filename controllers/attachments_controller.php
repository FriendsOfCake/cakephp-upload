<?php
class AttachmentsController extends UploadAppController {

	var $name = 'Attachments';

	function index() {
		$defaults = array(
			
		);
		$options = am($defaults, $this->params['named']);
		$conditions = array(
			
		);
		
		$this->Attachment->recursive = 0;
		$this->set('attachments', $this->paginate());
		$this->set('options', $options);
	}

	function view($id = null) {
		$defaults = array(
			
		);
		$options = am($defaults, $this->params['named']);
		$conditions = array(
			
		);
		
		if (!$id) {
			$this->Session->setFlash(__('Invalid attachment', true));
			$this->redirect(array('action' => 'index'));
		}
		$attachment = $this->Attachment->findBySlug($id);
		if(empty($attachment)) $attachment = $this->Attachment->read(null, $id);
		if(empty($attachment)) $this->redirect(array('action'=>'index'));
		$this->set('subtitle_for_layout', $attachment['Attachment']['name']);
		$this->set('attachment', $attachment);
		$this->set('options', $options);
	}

	function add() {
		$defaults = array(
			
		);
		$options = am($defaults, $this->params['named']);
		$conditions = array(
			
		);
		$this->set('options', $options);
		
		if (!empty($this->data)) {
			$params_named =  (!empty($this->data['Named'])) ? $this->data['Named'] : array();
			$this->set('params_named', $params_named);
			
			$this->Attachment->create();
			if ($this->Attachment->save($this->data)) {
				$this->Session->setFlash(__('The attachment has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The attachment could not be saved. Please, try again.', true));
			}
		} else {
			$params_named = $this->params['named'];
			$this->set('params_named', $params_named);
		}
	}

	function edit($id = null) {
		$defaults = array(
			
		);
		$options = am($defaults, $this->params['named']);
		$conditions = array(
			
		);
		$this->set('options', $options);
		
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid attachment', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			$params_named =  (!empty($this->data['Named'])) ? $this->data['Named'] : array();
			$this->set('params_named', $params_named);
			
			if ($this->Attachment->save($this->data)) {
				$this->Session->setFlash(__('The attachment has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The attachment could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Attachment->read(null, $id);
			$params_named = $this->params['named'];
			$this->set('params_named', $params_named);
		}
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for attachment', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Attachment->delete($id)) {
			$this->Session->setFlash(__('Attachment deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Attachment was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}
	function admin_index() {
		$defaults = array(
			
		);
		$options = am($defaults, $this->params['named']);
		$conditions = array(
			
		);
		
		$this->Attachment->recursive = 0;
		$this->set('attachments', $this->paginate());
		$this->set('options', $options);
	}

	function admin_view($id = null) {
		$defaults = array(
			
		);
		$options = am($defaults, $this->params['named']);
		$conditions = array(
			
		);
		
		if (!$id) {
			$this->Session->setFlash(__('Invalid attachment', true));
			$this->redirect(array('action' => 'index'));
		}
		$attachment = $this->Attachment->findBySlug($id);
		if(empty($attachment)) $attachment = $this->Attachment->read(null, $id);
		if(empty($attachment)) $this->redirect(array('action'=>'index'));
		$this->set('subtitle_for_layout', $attachment['Attachment']['name']);
		$this->set('attachment', $attachment);
		$this->set('options', $options);
	}

	function admin_add() {
		$defaults = array(
			
		);
		$options = am($defaults, $this->params['named']);
		$conditions = array(
			
		);
		$this->set('options', $options);
		
		if (!empty($this->data)) {
			$params_named =  (!empty($this->data['Named'])) ? $this->data['Named'] : array();
			$this->set('params_named', $params_named);
			
			$this->Attachment->create();
			if ($this->Attachment->save($this->data)) {
				$this->Session->setFlash(__('The attachment has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The attachment could not be saved. Please, try again.', true));
			}
		} else {
			$params_named = $this->params['named'];
			$this->set('params_named', $params_named);
		}
	}

	function admin_edit($id = null) {
		$defaults = array(
			
		);
		$options = am($defaults, $this->params['named']);
		$conditions = array(
			
		);
		$this->set('options', $options);
		
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid attachment', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			$params_named =  (!empty($this->data['Named'])) ? $this->data['Named'] : array();
			$this->set('params_named', $params_named);
			
			if ($this->Attachment->save($this->data)) {
				$this->Session->setFlash(__('The attachment has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The attachment could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Attachment->read(null, $id);
			$params_named = $this->params['named'];
			$this->set('params_named', $params_named);
		}
	}

	function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for attachment', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Attachment->delete($id)) {
			$this->Session->setFlash(__('Attachment deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Attachment was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}
}
?>