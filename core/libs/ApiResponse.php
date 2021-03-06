<?php

include __corePath.'libs/service.php';

class ApiResponse
{
  private $settings;
  
  function __construct($settingsdb, $serversdb, $taskdb, $userdb) 
  {
  $settings = new JsonDB($settingsdb);
  $this->settingsdb = $settingsdb;
  $this->serversdb = $serversdb;
  $this->taskdb = $taskdb;
  $this->userdb = $userdb;  
  
  $this->settings = $settings->data;
  }
  
  function response()
  {
  $action = $this->getAction();
  $params = $this->getParams();
  
  if(!$this->checkToken())die(json_encode(array('responseStatus' => 'Unathorized')));
  
  switch($action)
    {
    case 'serverinfo':
      return json_encode($this->getServerInfo());
      break;
    
    case 'taskslist':
      return json_encode($this->getTasksList());
      break;
    
    case 'tasksfiles':
      return json_encode($this->getTasksFiles());
      break;
    
    case 'downloadfile':
      return $this->downloadFile($params['taskId'], $params['fileName']);
      break;
    
    case 'updateTask':
      return json_encode($this->updateTask($params));
      break;
    
    case 'addTask':
      return json_encode($this->addTask($params));
      break;
    
    case 'deleteTask':
      return json_encode($this->deleteTask($params));
      break;
    }
  }
  
  function deleteTask($params)
  {
  $tasks = new JsonDB($this->taskdb);
  
  $id = $params['id'];
  
  if(!isset($tasks->data[$id])) return array('responseStatus' => 'error');
  
  unset($tasks->data[$id]);
  
  $tasks->saveToFile($this->taskdb);
  return array('responseStatus' => 'success', 'id' => $id);
  }
  
  function addTask($params)
  {
  $tasks = new JsonDB($this->taskdb);
  
  $receivedTask = json_decode(base64_decode($params['task']), true);
  $newId = time();
  $newTask = array('id' => $newId);
  
  foreach($receivedTask as $key => $val)
    $newTask[$key] = $val;
  
  $tasks->data[$newId] = $newTask;
  
  $tasks->saveToFile($this->taskdb);
  return array('responseStatus' => 'success', 'id' => $newId, 'task' => $tasks->data[$newId]);
  }
  
  function updateTask($params)
  {
  $tasks = new JsonDB($this->taskdb);
  if(!isset($tasks->data[$params['id']])) return array('responseStatus' => 'error');
  
  $receivedTask = json_decode(base64_decode($params['task']), true);
  
  foreach($receivedTask as $key => $val)
    $tasks->data[$params['id']][$key] = $val;
    
  $tasks->saveToFile($this->taskdb);
  return array('responseStatus' => 'success', 'task' => $tasks->data[$params['id']]);
  }
  
  function downloadFile($task, $fileName)
  {
  if(file_exists(__archiveDIR.'local/'.$task.'/'.$fileName))
    return file_get_contents(__archiveDIR.'local/'.$task.'/'.$fileName);
  return '';
  }
  
  
  function getTasksFiles()
  {
  $tasks = new JsonDB($this->taskdb);
  
  $res = array();
  foreach($tasks->data as $id => $task)
    {
    $files = glob(__archiveDIR.'local/'.$id.'/*');
    
    $filesRes = array();
    
    foreach($files as $key => $file)
      if( ($file !='.') && ($file !='..') && (!is_dir(__archiveDIR.'local/'.$id.'/'.$file)) )
        $filesRes[] = array('name' => str_replace(__archiveDIR.'local/'.$id.'/', '', $file), 'updated' => filemtime($file), 'size' => filesize($file));
    
    $res[$id] = $filesRes;
    }

  return $res;
  }
  
  function getTasksList()
  {
  $tasks = new JsonDB($this->taskdb);
  
  foreach($tasks->data as $key => $task)
    $tasks->data[$key]['memoryUsage'] = dirSize(__archiveDIR.'local/'.$task['id']);
  
  return $tasks->data;
  }
  
  function getServerInfo()
  {
  $tasks = new JsonDB($this->taskdb);
  
  $result = $this->settings;
  unset($result['apiKey']);
  $result['serverSoftware'] = $_SERVER['SERVER_SOFTWARE'];
  $result['documentRoot'] = $_SERVER['DOCUMENT_ROOT'];
  $result['freeSpace'] = disk_free_space(getcwd());
  $result['tasksCount'] = count($tasks->data);
  
  $tasks = $tasks->data;
  
  foreach($tasks as $key => $task)
    {
    $tasks[$key]['memoryUsage'] = dirSize(__archiveDIR.'local/'.$task['id']);
    }
  
  $result['tasks'] = $tasks;
  $result['responseStatus'] = 'ok';
  
  return $result;
  }
  
  function checkToken()
  {
  if(!isset($_REQUEST['token']))return 0;
  $params = $_REQUEST;
  unset($params['token']);
  $token = $this->genToken($params, $this->settings['apiKey']);
  if($token == $_REQUEST['token']) return 1;
  return 0;
  }
  
  function genToken($params, $apiKey)
  {
  $ar = $params;
  ksort($ar);
  $st = '';
  foreach($ar as $key => $val)
    $st.=$key.$val;
  return md5($st.$apiKey);
  }
  
  function getParams()
  {
  $params = $_REQUEST;
  
  unset($params['action']);
  unset($params['token']);
  
  return $params;
  }
  
  
  function getAction()
  {
  if(!isset($_REQUEST['action']))die('No action');
  
  return $_REQUEST['action'];
  }

  function __destruct() 
  {

  }  
}

?>