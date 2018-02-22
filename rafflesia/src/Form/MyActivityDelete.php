<?php

namespace Drupal\rafflesia\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\rafflesia\RafflesiaTasks;
use Drupal\user\Entity\User;

class MyActivityDelete extends ConfirmFormBase {

  /**
   * The ID of the item to delete.
   *
   * @var string
   */
  protected $id;
  
  /**
   * The Url of the last visited page
   * 
   * @var String 
   */
  
  private $path;
  
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rafflesia_myActivity_delete';
  }
 
  /**
  * {@inheritdoc}
  */
  public function getQuestion() {
    /* Get the last URL */
  
    $path = $this->getRequest()->headers->get('referer');
    $this->path = $path;
     
    return t('Do you want to delete %id?' . ' ', array('%id' => $this->id));
  }
  
  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    
   // $url =  Url::fromUri('rafflesia.report_today');
  $url =  new Url('rafflesia.report_today');
  
   return $url;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('Do this if you are sure.');
  }
  
  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

 /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return t('Cancel');
  }
  
  /**
   * {@inheritdoc}
   *
   * @param int $id
   *   (optional) The ID of the item to be deleted.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $this->id = $id;
    $user_id = \Drupal::currentUser()->id();
    $check_info = new RafflesiaTasks($this->id);
    
    $user =  User::load($id);
    
    $uid = $check_info->getId();
    
    // Auto Generate Field
    if($user_id == $uid){
    drupal_set_message('Redirect After Delete is set to Report Today. "Path Link update is Working on Progress" -Ron');
    return parent::buildForm($form, $form_state);
    
    }else{
      
      drupal_set_message('Access Denied: The Activity is not on your account access denied');
    } 
  }
   /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $id = $this->id;
    try { 
    $delete = db_delete('rafflesia_tasks')
      ->condition('id', $id)
      ->execute();
      } 
          catch(\Exception $e){
                drupal_set_message('Missing Database Exception '. $e->getMessage());
              }     
      $url = \Drupal\Core\Url::fromRoute('rafflesia.report_today');
      $form_state->setRedirectUrl($url);
      drupal_set_message("Delete Completed");                   
  }
  
} 