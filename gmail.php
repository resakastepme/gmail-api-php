<?php

class Gmail
{

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function readLabels()
    {

        $service = new Google_Service_Gmail($this->client);

        // Print the labels in the user's account.
        $user = 'me';
        $results = $service->users_labels->listUsersLabels($user);

        $the_html = "";

        if (count($results->getLabels()) == 0) {
            // print "No labels found.\n";
            $the_html.="<p> No Labels Found </p>"; 
        } else {
            // print "Labels:\n";
            $the_html.="<p> Labels: </p>"; 
            foreach ($results->getLabels() as $label) {
                // printf("- %s\n", $client->getRefreshToken());
                // printf("- %s\n", $label->getName());
                $the_html.="<p>".$label->getName()."</p>"; 
            }
        }

        return $the_html;
    }

    public function listMessage(){

        $service = new Google_Service_Gmail($this->client);

        // Print the labels in the user's account.
        $userId = 'me';

        $pageToken = NULL;
        $messages = array();
        $opt_param = array();

        $i=0;

        do {
            if($i==5){break;}
            $i++;
            try {
                if($pageToken){
                    $opt_param['pageToken'] = $pageToken;
                }
                $messagesResponse = $service->users_messages->listUsersMessages($userId, $opt_param);
                if($messagesResponse->getMessages()){
                    $messages = array_merge($messages, $messagesResponse->getMessages());
                    $pageToken = $messagesResponse->getNextPageToken();
                }
            } catch (Exception $e) {
                print 'An error occured: '.$e->getMessage();
            }
        } while ($pageToken);

        $j = 0;
        $decoded_msg = array();
        foreach ($messages as $message) {
            // print 'Message with ID: '.$message->getId().'<br/>';
            $msg = $service->users_messages->get($userId, $message->getId());

            $parts = $msg->getPayload()->getParts();
            if(count($parts) > 0){
                $data = $parts[1]->getBody()->getData();
            }else{
                $data = $msg->getPayload()->getBody()->getData();
            }

            // echo "<pre>".var_export($msg->payload->parts[1]->body->data, true)."</pre><hr>";
            $out = str_replace("-", "+", $data);
            $out = str_replace("_", "/", $out);
            $decoded_msg[] = base64_decode($out);
            // echo "<pre>".var_export($data, true)."</pre><hr>";
            $j++;
            if($j==5) {break;}
        }
        return $decoded_msg;
    }

}
