<?php
/**
 * @file
 * Contains \Drupal\rafflesia\Form\MyActivityEdit
 * 
 */


namespace Drupal\rafflesia\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rafflesia\RafflesiaActivityListing;
use Drupal\rafflesia\RafflesiaUserInformation;
use Drupal\Core\Url;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\rafflesia\RafflesiaTasks;

/*
 * Create the Form Notice
 */

class MyActivityEdit extends FormBase{
    
 
 private $path; 
 
 
 private $route_name;
  
 /*
 * {@inheritdoc}
 */
      
  public function getFormId(){
    return 'editMyForm';
  }
  
  /*
   * {@inheritdoc}
   */
    
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL){
    
    
    $user = \Drupal::currentUser()->id();
    $cast_id = $id;
    
   
    
    /* Get Information if Manager */
    $user_id = new RafflesiaTasks($cast_id);
    
    
    
    if($user != $user_id->getId()){
      drupal_set_message('Access Denied: The Activity is not on your account.');
    }else {
      
      // list of my wsm 
      $form['message_top'] = [
           '#markup' => $this->t('Edit This : @id, <br><br>', [ '@id' => $id ] ),
      ];
      
      $user_info = new RafflesiaUserInformation($user);
      $activities = new RafflesiaActivityListing($user);

      $results = [];
      $managerid = [];
      $activities_data = [];
      $default_act = [];
      
      /* Check if The user is a Manager */
      if($user_info->checkUserManager()){

       $form['message'] = array(
           '#markup' => $this->t("This is My List of Activities As Im A Manager <br>"),
        );   
       $activities_data = $activities->getManActList();

       foreach ($activities_data as  $entry){
          $results[$entry['id']] = $entry['activity_name'];

       }
        $managerid = $user;

      }else{
        $activities_data = $activities->getActList(); 
        foreach ($activities_data as  $entry){
          $results[$entry['id']] = $entry['activity_name'];
          $managerid = $entry['managerid'];
        }
      }

      $default_act = $activities->getAct($id);

      $form['activity_name'] = array(
        '#type' => 'select',
        '#title' => t('Activity Name'),
        '#options' => $results,
        '#default_value' => $default_act['activity_id'] ,
        '#required' => TRUE,
      );

      /* Data And Time Format */

      $date_format = 'd-m-Y';
      $time_format = 'h:i A'; 
      $today = date($date_format . ' ' . $time_format  ,  $default_act['tasks_timestamp']);
      
      /* Date Form */

      $form['date_time'] = array(
        '#type' => 'datetime',
        '#title' => t('Date  Time'),  
        '#date_time_format' => $date_format .''. $time_format ,
        '#description' => '* enter the new date and time', 
        '#default_value' => new DrupalDateTime($today),  
        '#required' => TRUE,  
      );

       $form['activity_subject'] = array(
        '#type' => 'textfield',
        '#title' => 'Subject',
        '#maxlength' => 500,
         '#default_value' => $default_act['tasks_subject'],
        '#required' => TRUE, 
      );

      $form['volume'] = array(
        '#type' => 'number',
        '#title' => t('Volume'),
        '#min' => 1,
        '#max' => 100,
        '#required' => TRUE,
        '#default_value' => $default_act['tasks_volume'], 
      );

       // Extra actions for the display.
      $form['actions']['other_action'] = [
        '#type' => 'dropbutton',

      ];


      $form['actions']['cancel'] = array(
        '#type' => 'submit',
        '#submit' => array([$this, 'cancelForm']),
        '#value' => t('Cancel'),
      );

      $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Save Update'),
      );

      $form['id'] = array(
        '#type' => 'hidden',
        '#value' => $id,
      );

      try{ 
      $path = $this->getRequest()->headers->get('referer');
      $this->path = $path;

      $url = new Url;

      $this->route_name = $url->fromUri($this->path)->getRouteParameters();

      print_r($this->route_name);

        }catch (\Exception $e){
          drupal_set_message('This is a test Path, The current path to the correct area '
              . ' is not working as of this time "Ron" Exception: '. $e->getMessage() . '  ' . $this->route_name);

          drupal_set_message('Note : Cancel and save will redirect you to report today');

        }
    }
    return $form;   
  }
  public static function cancelForm(array &$form, FormStateInterface $form_state) {
    
    /* Get the last path */
   $url = \Drupal\Core\Url::fromRoute('rafflesia.report_today');
   $form_state->setRedirectUrl($url);
   
  }
  
  
   /*
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
     
    $timeStamp =  strtotime($form_state->getValue('date_time'));
    
    db_update('rafflesia_tasks')
      ->fields( array(
        'activity_id' => $form_state->getValue('activity_name'), 
        'tasks_subject' => $form_state->getValue('activity_subject'),
        'tasks_volume' => $form_state->getValue('volume'),
        'tasks_date' => date('Y-m-d',$timeStamp) ,
        'tasks_timestamp' => $timeStamp,
      ))
      ->condition('id',$form_state->getValue('id') , '=')
    ->execute();
     
     $url = \Drupal\Core\Url::fromRoute('rafflesia.report_today');
     $form_state->setRedirectUrl($url);
     
    drupal_set_message(t('Edit Completed on Task'));
   
  }

}