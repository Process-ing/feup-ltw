<?php
declare(strict_types=1);

require_once __DIR__ . '/../framework/Autoload.php';
require_once __DIR__ . '/../db/utils.php';
require_once __DIR__ . '/utils.php';


$db = getDatabaseConnection();
$request = new Request();
$session = new Session();

$method = $request->getMethod();
$endpoint = $request->getEndpoint();

header('Content-Type: application/json');

switch ($method) {
    case 'GET':
        if (preg_match('/^\/api\/message\/(\d+)\/(\d+)\/?$/', $endpoint, $matches)) {
            $userId1 = (int)$matches[1];
            $userId2 = (int)$matches[2];
            $user1 = User::getUserById($db, $userId1);
            $user2 = User::getUserById($db, $userId2);
            if ($user == null || $user2 == null)
                sendNotFound();

            if (!$request->verifyCsrf())
                sendCrsfMismatch();
            if (!userLoggedIn($request))
                sendUserNotLoggedIn();

            $sessionUser = $session->get('user');
            if ($sessionUser['id'] != $userId1 && $sessionUser['id'] != $userId2)
                sendUnauthorized('Only the sender and the receiver can see the respective messages');

            $lastId = $request->get('last_id');
            $lastId = $lastId && !filter_var($lastId, FILTER_VALIDATE_INT) ? (int)$lastId : 20;
            $messages = parseMessages(Message::getMessages($db, $user1, $user2, $lastId));
            
            sendOk(['messages' => $messages]);
        } else {
            sendNotFound();
        }
    
    case 'POST':
        if (preg_match('/^\/api\/message\/(\d+)\/?$/', $endpoint, $matches)) {
            $senderId = (int)$matches[1];
            $sender = User::getUserById($db, $userId1);
            if ($sender == null)
                sendNotFound();

            if (!$request->verifyCsrf())
                sendCrsfMismatch();
            if (!userLoggedIn($request))
                sendUserNotLoggedIn();
            
            $sessionUser = getSessionUser($request);
            if ($sessionUser['id'] != $senderId)
                sendUnauthorized('Only the sender can send a message');
            if (!$request->paramsExist(['receiver_id', 'content']))
                sendMissingFields();

            $receiverId = (int)$request->post('receiver_id');
            $receiver = User::getUserById($db, $receiverId);
            if ($receiver == null)
                sendBadRequest('Receiver does not exist');

            if ($user['id'] == $receiverId)
                sendBadRequest('User cannot send a message to themselves');

            try {
                $message = storeMessage($request, $db, $sender, $receiver);
            } catch (Exception $e) {
                error_log($e->getMessage());
                sendInternalServerError();
            }

            sendCreated([
                'links' => [
                    [
                        'rel' => 'messages',
                        'href' => $request->getServerHost() . '/api/message/' . $senderId . '/' . $receiverId . '/',
                    ]
                ]
            ]);
        } else {
            sendNotFound();
        }

    default:
        sendMethodNotAllowed();
}