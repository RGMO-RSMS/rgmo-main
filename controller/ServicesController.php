<?php

ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

header("Access-Control-Allow-Methods: POST");
header('Content-Type: application/json; charset=utf-8');

include_once  __DIR__ . '/../includes/config/database.php';
include_once __DIR__ . '/../objects/session.php';
include_once __DIR__ . '/../objects/services.php';
include_once __DIR__ . '/../objects/profile.php';
include_once __DIR__ . '/EmailController.php';
include_once __DIR__ . '/ProfileController.php';

$DATABASE = new Database();
$db = $DATABASE->getConnection();

function services($db) {
    $SERVICES = new Services($db);
    $SERVICES->get_services();
    return json_encode($SERVICES->services);
}

function service_type($db) {
    $SERVICES = new Services($db);
    $SERVICES->service_name = $_POST['service_name'];
    $SERVICES->getServiceId();
    return json_encode($SERVICES->idGetType());
}

function servicesSelection($data) {
    $selection = [];
    if(isset($_POST['search'])) {
        $selection[] = ['id' => $_POST['search'], 'text' => $_POST['search']];
    }
    foreach($data as $key => $value) {
        $selection[] = ['id' => $value->service_id, 'text' => $value->service_name];
    }
    return json_encode($selection);
}

function service_info($db) {
    $SERVICES = new Services($db);
    $SERVICES->type_id = $_POST['type_id'];
    $SERVICES->getServiceInfo();
    return json_encode($SERVICES);
}

function deleteService($db) {
    try {
        $SERVICES = new Services($db);
        $SERVICES->type_id = $_POST['type_id'];
        $SERVICES->deleteService();
        return json_encode(['status' => true]);
    }
    catch(Exception $e) {
        return json_encode(['status'=> false,'message'=> $e->getMessage()]);
    }
}

function allServiceType($db) {

    $h = 200;
    $folder_path = __DIR__ . '/../includes/images/';
    $SERVICES = new Services($db);
    $data = $SERVICES->getAllType();
    foreach($data as $key => $value) {
        $size = explode(" ", getimagesize($folder_path . $value['service_image'])[3]);
        $height = (int)filter_var($size[1], FILTER_SANITIZE_NUMBER_INT);
        $h = ($height < $h) ? $height : $h;
        $data[$key]['f_price'] = number_format($value['price'], 0, '', ',');
    }
    return json_encode(['data' => $data, 'height' => $h]);
}

function pending_request($db) {

    $SERVICES = new Services($db);
    $pending_request = $SERVICES->getPendingRequest();

    // Get Client Info
    foreach($pending_request as $key => $value) {

        // Get Client Id Data
        $PROFILE = new Profile($db, $value['client_id']);
        $pending_request[$key]['client_name'] = $PROFILE->firstname . " " . $PROFILE->lastname;
        
        // Get Service Id Datad
        $SERVICES->type_id = $value['service_id'];
        $SERVICES->getServiceInfo();
        $pending_request[$key]['service_name'] = $SERVICES->type_name;
        $pending_request[$key]['service_price'] = $SERVICES->true_price;
        $pending_request[$key]['f_price'] = number_format($SERVICES->true_price, 2, '.', ',');

        // Action
        $pending_request[$key]['action'] = 'buttons';

    }// foreach

    return json_encode($pending_request);

}// pending request

function getServiceAvailability($db) {
    $SERVICES = new Services($db);
    $SERVICES->availability_status = $_POST['status'];
    $data = $SERVICES->getServiceAvailability();
    foreach($data as $key => $value) {
        $data[$key]['f_price'] = number_format($value['price'], 2, '.', ',');
    }
    return json_encode($data);
}

function client_request($db) {
    $SERVICES = new Services($db);
    $SERVICES->client_id = $_POST['user_id'];
    $SERVICES->status = $_POST['status'];
    $SERVICES->type_id = $_POST['type_id'];
    $result = $SERVICES->submitRequest();

    if($result['status']) {
        // Send Email after Submission
        $client_info = json_decode(getInfo($SERVICES->client_id, $db));
        $SERVICES->getServiceInfo();
        $subject = "Rental Request Submission Confirmation";
        $message = "
            Dear Customer ".$client_info->first_name.", <br/>
            We are pleased to inform you that we have received your rental request for our monitoring system. <br/>
            You request has been successfully submitted and is currently being processed. <br/>

            <b> Rental Request Details: </b> <br/>
            <ul>
                <li>Requested Item: ".$SERVICES->type_name." </li>
                <li>Request Date: ".date("F j, Y")." </li>
            </ul>

            We will review you request and provide you with further details shortly. If you have any questions or<br/>
            need immediate assistance, please do not hesitate to contact our customer service team. <br/> <br/>
            Thank you for choosing our Rental Services. We look forward to continuing to serve you.
        ";
        sendEmail($client_info->email, $subject, $message);
        return json_encode($result);
    }
}

function occupiedSlots($db) {
    $SERVICES = new Services($db);
    $SERVICES->availability_status = $_POST['availability'];
    $data = $SERVICES->getServiceAvailability();
    foreach($data as $key => $value) {
        $data[$key]['f_price'] = number_format($value['price'], 2, '.', ',');
    }
    return json_encode($data);
}

function submitPayment($db) {

    try {

        // Check Payment Status
        switch($_POST['status']) {

            // Insert new data in tbl_payments
            case 'Pending':

                $SERVICES = new Services($db);
                $SERVICES->price = $_POST['service_price'];
                $SERVICES->total_paid = $_POST['payment'];
                $SERVICES->due_date = '';
                $SERVICES->form_id = $_POST['form_id'];
                $SERVICES->client_id = $_POST['client_id'];
                $SERVICES->payment = $_POST['payment'];
                $SERVICES->payment_balance = $SERVICES->price - $SERVICES->total_paid;
                $SERVICES->type_id = $_POST['service_id'];
                $SERVICES->availability_status = 'no';
                $SERVICES->date = date('Y-m-d');
                $SERVICES->insertClientPayment();

                // Client Info
                $client_info = json_decode(getInfo($SERVICES->client_id, $db));
                
                // Send Email after Submission
                $subject = "Payment Receipt for Your Rental";
                $message = "
                    Dear Customer ".$client_info->first_name.", <br/>
                    We hope this message finds you well. Thank you for your recent payment for the Rental. <br/>
                    We are pleased to inform you that you payment has been successfully processed. <br/>
                    <b> Payment Details: </b> <br/>
                    <ul>
                        <li>Amount Paid: PHP ".number_format($SERVICES->payment, 0, '', ',')." </li>
                        <li>Remaining Balance: PHP ".number_format($SERVICES->payment_balance, 0, '', ',')." </li>
                    </ul>
                    We appreciate your prompt payment. If you have any questions or need further assistance, please do <br/>
                    not hesitate to contact our customer service team. <br/> <br/>
                    Thank you for choosing our Rental Services. We look forward to continuing to serve you.
                ";
                sendEmail($client_info->email, $subject, $message);

            break;

            case 'Client Payment':

                $SERVICES = new Services($db);
                $SERVICES->client_id = $_POST['client_id'];
                $SERVICES->payment = $_POST['payment'];
                $SERVICES->payment_balance = $_POST['remaining_balance'] - $SERVICES->payment;
                $SERVICES->payment_id = $_POST['payment_id'];
                $SERVICES->total_paid = $_POST['total_paid'] + $SERVICES->payment;
                $SERVICES->date = date('Y-m-d');
                $SERVICES->updatePayment();

                // Check if Rental is Fully Paid
                if($SERVICES->payment_balance == 0) {
                    $SERVICES->status = 'Paid';
                    $SERVICES->form_id = $_POST['form_id'];
                    $SERVICES->updateStatus();
                }

                // Client Info
                $client_info = json_decode(getInfo($SERVICES->client_id, $db));
                
                // Send Email after Submission
                $subject = "Payment Receipt for Your Rental";
                $message = "
                    Dear Customer ".$client_info->first_name.", <br/>
                    We hope this message finds you well. Thank you for your recent payment for the Rental. <br/>
                    We are pleased to inform you that you payment has been successfully processed. <br/>
                    <b> Payment Details: </b> <br/>
                    <ul>
                        <li>Amount Paid: PHP ".number_format($SERVICES->payment, 0, '', ',')." </li>
                        <li>Remaining Balance: PHP ".number_format($SERVICES->payment_balance, 0, '', ',')." </li>
                    </ul>
                    We appreciate your prompt payment. If you have any questions or need further assistance, please do <br/>
                    not hesitate to contact our customer service team. <br/> <br/>
                    Thank you for choosing our Rental Services. We look forward to continuing to serve you.
                ";
                sendEmail($client_info->email, $subject, $message);

            break;

        }// switch

        return json_encode(['status' => true]);

    }catch(Exception $e) {
        return json_encode(['status' => false, 'message' => $e->getMessage()]);
    }

}// submit payment

function allClientPayments($db) {

    $SERVICES = new Services($db);
    $payments_data = $SERVICES->getAllPaymentLogs();
    $row_counter = 1;
    $SES = Session::getInstance();
    $id = $SES->id;
    $admin_profile = json_decode(getInfo($id, $db));

    foreach($payments_data as $key => $value) {

        $SERVICES->form_id = $value['form_id'];
        $client_form = $SERVICES->getClientFormData();

        // Get Name of Client
        $PROFILE = new Profile($db, $client_form['client_id']);
        $payments_data[$key]['numbering'] = $row_counter;
        $payments_data[$key]['client_id'] = $client_form['client_id'];
        $payments_data[$key]['client_name'] = $PROFILE->firstname . " " . $PROFILE->lastname;
        $payments_data[$key]['client_email'] = $PROFILE->email;
        $payments_data[$key]['contact_number'] = $PROFILE->contact_number;
        $payments_data[$key]['encoder_name'] = $admin_profile->first_name;

        // Get Service Data
        $SERVICES->type_id = $client_form['service_id'];
        $SERVICES->getServiceInfo();
        $payments_data[$key]['service_id'] = $client_form['service_id'];
        $payments_data[$key]['service_name'] = $SERVICES->type_name;
        $payments_data[$key]['location'] = $SERVICES->location;
        $payments_data[$key]['remaining_balance'] = $value['service_price'] - $value['total_paid'];
        $payments_data[$key]['status'] = $client_form['status'];

        // Format to Money Format
        $payments_data[$key]['f_price'] = number_format($value['service_price'], 0, '', ',');
        $payments_data[$key]['f_rbalance'] = number_format($payments_data[$key]['remaining_balance'], 0, '', ',');
        $payments_data[$key]['f_tpaid'] = number_format($value['total_paid'], 0, '', ',');
        $payments_data[$key]['f_ldate'] = date("F j, Y", strtotime($value['log_date']));

        $row_counter = $row_counter + 1;

    }// foreach

    return json_encode($payments_data);

}// paid client

function paidClient($db) {

    $SERVICES = new Services($db);
    $payments_data = $SERVICES->getAllPaymentData();
    $row_counter = 1;
    $SES = Session::getInstance();
    $id = $SES->id;
    $admin_profile = json_decode(getInfo($id, $db));
    $sum_price = 0;
    $sum_paid = 0;
    $sum_balance = 0;

    foreach($payments_data as $key => $value) {

        $SERVICES->form_id = $value['form_id'];
        $client_form = $SERVICES->getClientFormData();

        // Get Name of Client
        $PROFILE = new Profile($db, $client_form['client_id']);
        $payments_data[$key]['numbering'] = $row_counter;
        $payments_data[$key]['client_id'] = $client_form['client_id'];
        $payments_data[$key]['client_name'] = $PROFILE->firstname . " " . $PROFILE->lastname;
        $payments_data[$key]['client_email'] = $PROFILE->email;
        $payments_data[$key]['contact_number'] = $PROFILE->contact_number;
        $payments_data[$key]['encoder_name'] = $admin_profile->first_name;

        // Get Service Data
        $SERVICES->type_id = $client_form['service_id'];
        $SERVICES->getServiceInfo();
        $payments_data[$key]['service_id'] = $client_form['service_id'];
        $payments_data[$key]['service_name'] = $SERVICES->type_name;
        $payments_data[$key]['location'] = $SERVICES->location;
        $payments_data[$key]['remaining_balance'] = $value['service_price'] - $value['total_paid'];
        $payments_data[$key]['status'] = $client_form['status'];

        // Format to Money Format
        $payments_data[$key]['f_ldate'] = date("F j, Y", strtotime($client_form['date']));
        $payments_data[$key]['f_price'] = number_format($value['service_price'], 2, '.', ',');
        $payments_data[$key]['f_tpaid'] = number_format($value['total_paid'], 2, '.', ',');
        $payments_data[$key]['f_rbalance'] = number_format($payments_data[$key]['remaining_balance'], 2, '.', ',');

        $row_counter = $row_counter + 1;

    }// foreach

    return json_encode($payments_data);

}// paid client

function getPaymentHistory($db) {

    $SERVICES = new Services($db);
    $SERVICES->client_id = $_POST['client_id'];
    
    $data = $SERVICES->getPaymentLogs();

    foreach($data as $key => $value) {
        
        // Get Name of Id
        $PROFILE = new Profile($db, $SERVICES->client_id);
        $data[$key]['client_name'] = $PROFILE->firstname . " " . $PROFILE->lastname;

        // Get Service Name
        $SERVICE_DATA = new Services($db);
        $SERVICE_DATA->form_id = $value['form_id'];
        $SERVICE_DATA->type_id = $SERVICE_DATA->getClientFormData()['service_id'];
        $SERVICE_DATA->getServiceInfo();
        $data[$key]['type_name'] = $SERVICE_DATA->type_name;
        $data[$key]['location'] = $SERVICE_DATA->location;
        $data[$key]['price'] = $SERVICE_DATA->price;
        $data[$key]['description'] = $SERVICE_DATA->description;
        $data[$key]['availability_status'] = $SERVICE_DATA->availability_status;
        $data[$key]['service_id'] = $SERVICE_DATA->service_id;
        $data[$key]['balance'] = $value['service_price'] - $value['total_paid'];

    }// foreach

    return json_encode(['data' => $data]);

}// get payment history

function getClientPayments($db) {

    $SERVICES = new Services($db);
    $data = $SERVICES->getClientPayments();

    foreach($data as $key => $value) {
        
        // Get Name of Id
        $PROFILE = new Profile($db, $value['client_id']);
        $data[$key]['client_name'] = $PROFILE->firstname . " " . $PROFILE->lastname;

        // Get Service Name
        $SERVICE_DATA = new Services($db);
        $SERVICE_DATA->form_id = $value['form_id'];
        $SERVICE_DATA->type_id = $SERVICE_DATA->getClientFormData()['service_id'];
        $SERVICE_DATA->getServiceInfo();
        $data[$key]['type_name'] = $SERVICE_DATA->type_name;
        $data[$key]['location'] = $SERVICE_DATA->location;
        $data[$key]['price'] = $SERVICE_DATA->price;
        $data[$key]['description'] = $SERVICE_DATA->description;
        $data[$key]['availability_status'] = $SERVICE_DATA->availability_status;
        $data[$key]['service_id'] = $SERVICE_DATA->service_id;
        $data[$key]['balance'] = $value['service_price'] - $value['total_paid'];

        // Format to Money Format
        $data[$key]['f_price'] = number_format($value['service_price'], 2, '.', ',');
        $data[$key]['f_tpaid'] = number_format($value['total_paid'], 2, '.', ',');
        $data[$key]['f_payment'] = number_format($value['payment'], 2, '.', ',');
        $data[$key]['f_pbalance'] = number_format($value['payment_balance'], 2, '.', ',');
        $data[$key]['f_ldate'] = date("F j, Y", strtotime($value['log_date']));

    }// foreach

    return json_encode(['data' => $data]);

}// get payment history

// Get this Logged In User Payment
function getUserPayments($db) {

    $SES = Session::getInstance();
    $id = $SES->id;
    
    $query = "SELECT A.*, B.* FROM tbl_payment_logs as A
            LEFT JOIN tbl_payments as B
            ON A.payment_id = B.payment_id
            WHERE A.client_id = ?
            ORDER BY A.logs_id DESC
        ";

    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->closeCursor();
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $row_counter = 1;

    foreach($data as $key => $value) {

        // Log Date
        $log_date = explode("-", $value["log_date"]);
        $data[$key]['number'] = $row_counter;

        // No Filter Yet
        if($_POST['filter'] == 0 || $_POST['filter'] == "") {

            // Get Name of Id
            $PROFILE = new Profile($db, $value['client_id']);
            $data[$key]['client_name'] = $PROFILE->firstname . " " . $PROFILE->lastname;

            // Log Date Format
            $data[$key]['log_date_format'] = date("F j, Y", strtotime($value['log_date']));

            // Get Service Name
            $SERVICE_DATA = new Services($db);
            $SERVICE_DATA->form_id = $value['form_id'];
            $SERVICE_DATA->type_id = $SERVICE_DATA->getClientFormData()['service_id'];
            $SERVICE_DATA->getServiceInfo();
            $data[$key]['type_name'] = $SERVICE_DATA->type_name;
            $data[$key]['location'] = $SERVICE_DATA->location;
            $data[$key]['price'] = $SERVICE_DATA->price;
            $data[$key]['description'] = $SERVICE_DATA->description;
            $data[$key]['availability_status'] = $SERVICE_DATA->availability_status;
            $data[$key]['service_id'] = $SERVICE_DATA->service_id;
            $data[$key]['balance'] = $value['service_price'] - $value['total_paid'];
            $data[$key]['f_price'] = number_format($value['service_price'], 2,'.',',');
            $data[$key]['f_balance'] = number_format($value['service_price'] - $value['total_paid'], 2,'.', ',');
            $data[$key]['f_payment'] = number_format($value['payment'], 2, '.', ',');

        }
        // Equal to Year Selected
        else if($log_date[0] == $_POST['filter']) {

            // Get Name of Id
            $PROFILE = new Profile($db, $value['client_id']);
            $data[$key]['client_name'] = $PROFILE->firstname . " " . $PROFILE->lastname;

            // Log Date Format
            $data[$key]['log_date_format'] = date("F j, Y", strtotime($value['log_date']));

            // Get Service Name
            $SERVICE_DATA = new Services($db);
            $SERVICE_DATA->form_id = $value['form_id'];
            $SERVICE_DATA->type_id = $SERVICE_DATA->getClientFormData()['service_id'];
            $SERVICE_DATA->getServiceInfo();
            $data[$key]['type_name'] = $SERVICE_DATA->type_name;
            $data[$key]['location'] = $SERVICE_DATA->location;
            $data[$key]['price'] = $SERVICE_DATA->price;
            $data[$key]['description'] = $SERVICE_DATA->description;
            $data[$key]['availability_status'] = $SERVICE_DATA->availability_status;
            $data[$key]['service_id'] = $SERVICE_DATA->service_id;
            $data[$key]['balance'] = $value['service_price'] - $value['total_paid'];
            $data[$key]['f_balance'] = number_format($value['service_price'] - $value['total_paid'], 0,'', ',');
            $data[$key]['f_payment'] = number_format($value['payment'], 0, '', ',');

        }
        else if($log_date[0] != $_POST['filter']) {
            $data = '';
        }

        $row_counter = $row_counter + 1;

    }// foreach

    return json_encode(['data' => $data]);

}// get user payments

function clientRentView($db) {

    $SES = Session::getInstance();
    $id = $SES->id;
    $SERVICES = new Services($db);
    $SERVICES->client_id = $id;
    $data = $SERVICES->getClientRental();
    $row_counter = 1;

    foreach($data as $key => $value) {
        $data[$key]['number'] = $row_counter;
        $data[$key]['f_price'] = number_format($value['price'], 2, '.', ',');
        $row_counter = $row_counter + 1;
    }// foreach

    return json_encode(['data' => $data]);

}// client rent view

function financialReports($db) {

    $SERVICES = new Services($db);
    $SERVICES->get_services();
    $payments = $SERVICES->getAllPaymentData();

    // List of Service Connect Payments
    foreach($SERVICES->services as $key => $service) {

        $counter = 0;

        if(!empty($payments)) {

            foreach($payments as $k => $value) { 
        
                $FORM_SERVICE = new Services($db);
                $FORM_SERVICE->form_id = $value['form_id'];
                $FORM_SERVICE->type_id = $FORM_SERVICE->getClientFormData()['service_id'];
                $FORM_SERVICE->service_id = $FORM_SERVICE->typeIdGetService()['service_id'];
    
                // If List of Service Name is Equal to Paid Service Name add 1 count
                if($service['service_name'] == $FORM_SERVICE->getListOfService()['service_name']) {
                    $counter = $counter + 1;
                }
                
            }// payments foreach
            
        }// if

        // Add Counted Collection
        $SERVICES->services[$key]['count'] = $counter;

    }// services foreach

    return json_encode(["data" => $SERVICES->services]);

}// financial repoerts

function chartReport($db) {

    $SERVICES = new Services($db);
    $data = $SERVICES->getAllClientForm();
    $current_year = date("Y");
    $months = [
        ["text" => "January", "value" => 0], 
        ["text" => "February", "value" => 0], 
        ["text" => "March", "value" => 0], 
        ["text" => "April", "value" => 0], 
        ["text" => "May", "value" => 0], 
        ["text" => "June", "value" => 0], 
        ["text" => "July", "value" => 0], 
        ["text" => "August", "value" => 0], 
        ["text" => "September", "value" => 0], 
        ["text" => "October", "value" => 0], 
        ["text" => "November", "value" => 0], 
        ["text" => "December", "value" => 0] 
    ];

    foreach($data as $key => $value) {

        if(!is_null($value['date'])) {
            // Date
            $y = date("Y", strtotime($value['date']));
            $m = date("m", strtotime($value['date']));
            
            foreach($months as $k => $month) { 

                // If Data Year is Equal to Current Year
                if($y == $current_year) {
                    // Same Month
                    if($m == $k + 1) { $months[$k]['value'] = $month['value'] + 1; }
                }
        
            }// foreach months
        }// date

    }// foreach data

    return json_encode(array_column($months, "value"));

}// chart reports

function yearSelection() {
    $years = [];
    $start_year = 2000;
    $end_year = date("Y");
    for($i = $end_year; $i >= $start_year; $i--) {
        $years[] = ["id" => $i, "text" => $i];
    }
    return json_encode($years);
}// year selection

function serviceAvailability($db) {

    // boolean
    $isAvailable = false;

    $SERVICES = new Services($db);
    $SERVICES->service_id = $_POST['id'];
    
    foreach($SERVICES->checkServiceAvailability() as $key => $value) {
        if($value['availability_status'] == "yes") {
            $isAvailable = true;
            break;
        }
    }// foreach

    return json_encode($isAvailable);

}// service availability

function addService($db) {

    try {

        $target_dir = __DIR__ . "/../includes/images/";
        $target_file = $target_dir . basename($_FILES["service_image"]["name"]);
        $file_name = $_FILES["service_image"]["name"];

        // Check if file already exists
        if (file_exists($target_file)) {
            throw new Exception("File Name Already Exists! Try other Name!");
        }

        // Upload Image
        if(move_uploaded_file($_FILES["service_image"]["tmp_name"], $target_file)) { 

            $SERVICES = new Services($db);
            $service_id = $_POST['service_name'];

            // If New List of Service is Added
            if(!is_int($_POST['service_name'])) {
                $SERVICES->service_name = $_POST['service_name'];
                $service_id = $SERVICES->insertService();
            }

            $SERVICES->type_name = $_POST['type_name'];
            $SERVICES->location = $_POST['location'];
            $SERVICES->price = $_POST['price'];
            $SERVICES->description = $_POST['description'];
            $SERVICES->availability_status = "yes";
            $SERVICES->service_image = $file_name;
            $SERVICES->service_id = $service_id;
            $SERVICES->addService();

            return json_encode(['status' => true]);

        }// upload image
        else {
            throw new Exception("Something wrong with uploading your image! Please Try Again!");
        }
        

    }catch(Exception $e) {
        return json_encode(['status' => false, 'message' => $e->getMessage()]);
    }

}// add service

function displayUpdateService($db) {

    $SERVICES = new Services($db);
    $SERVICES->service_name = $_POST['service_id'];
    $SERVICES->getServiceId();
    $selections = [];

    $services = json_decode(services($db));
    $services_selection = json_decode(servicesSelection($services));
    $service_info = json_decode(service_info($db));

    foreach($services_selection as $k => $v) {
        if($v->id == $SERVICES->service_id) {
            $selections[] = ['id' => $v->id, 'text' => $v->text, 'selected' => true];
        }
        else {
            $selections[] = ['id' => $v->id, 'text' => $v->text];
        }
    }

    $service_info->selected_service = $selections;
    return json_encode($service_info);

}// display update service

function updateService($db) {
    try {
        $SERVICES = new Services($db);
        $SERVICES->type_name = $_POST['type_name'];
        $SERVICES->location = $_POST['location'];
        $SERVICES->price = $_POST['price'];
        $SERVICES->description = $_POST['description'];
        $SERVICES->service_id = $_POST['service_name'];
        $SERVICES->type_id = $_POST['type_id'];
        $SERVICES->updateService();

        return json_encode(['status' => true]);

    }
    catch(Exception $e) {
        return json_encode(['status'=> false,'message'=> $e->getMessage()]);
    }
}// update service

function updateServiceImage($db) {
    try {

        // Get Selected Service Image
        $SERVICES = new Services($db);
        $SERVICES->type_id = $_POST['type_id'];
        $SERVICES->getServiceInfo();

        // Update Image
        $target_dir = __DIR__ . "/../includes/images/";
        $target_file = $target_dir . basename($_FILES["service_image"]["name"]);

        // Update in FileSystem
        if(move_uploaded_file($_FILES["service_image"]["tmp_name"], $target_file)) { 
            
        }// move uploaded files
        else {
            throw new Exception("Not Uploaded");
        }

        $SERVICE_IMAGE = new Services($db);
        $SERVICE_IMAGE->service_image = $_FILES["service_image"]["name"];;
        $SERVICE_IMAGE->type_id = $_POST['type_id'];
        $SERVICE_IMAGE->updateImage();

        // Delete File in System
        unlink($target_dir . $SERVICES->service_image);
        
        return json_encode(['status' => true]);

    }
    catch(Exception $e) {
        return json_encode(['status'=> false,'message'=> $e->getMessage()]);
    }

}// update image

function allTypesTableWithFiltering($db) {

    $data = json_decode(allServiceType($db))->data;
    $new_data = [];
    $row_counter = 1;

    foreach($data as $key => $value) {
        $data[$key]->number = $row_counter;
        $data[$key]->decimal_price = number_format($value->price,2,'.',',');
        $row_counter = $row_counter + 1;
    }

    return json_encode(['data' => ($_POST['isTrue'] == 1) ? $new_data : $data]);


}// filtering

// function dashboardFiltering($db){
//     $data = [];
//     $SERVICES = new Services($db);
//     foreach($SERVICES->dashboardFiltering()as $key => $value) {
//     $data[]=["id"=>$value['type_name'],"text"=>$value['type_name']];
//     }
//     return json_encode($data);
    
// }

function dashboardSelection($db){
    $data = [];
    $SERVICES = new Services($db);
    foreach($SERVICES->dashboardSelection()as $key => $value){
        $data[]=["id"=>$value['type_name'],"text"=>$value['type_name']];
    }
    return json_encode($data);
}

function filterDashboard($db,$filter,$istrue){
    if($istrue == 1) {
        $data = [];
        foreach(json_decode(paidClient($db)) as $key => $value) {
            if ($filter == $value->service_name){
                $data[] = $value;
            }
        }
    }else{
        $data = json_decode(paidClient($db));
    }
    return json_encode(["data"=> $data]);
}



switch($_POST['case']) {

    // Get All Services
    case 'services': echo services($db); break;
    // Get Type of Service depends on service_id
    case 'fetch type': echo service_type($db); break;
    // Get Info of Type
    case 'service info': echo service_info($db); break;
    // Get All Type of Services
    case 'all types': echo allServiceType($db); break;
    // All Services Types DataTable
    case 'all types table': echo allTypesTableWithFiltering($db); break;
    // Get All Pending Request
    case 'pending request': echo pending_request($db); break;
    // Logged in Client Submit Request
    case 'client request': echo client_request($db); break;
    // Get All Available Services
    case 'available service': echo getServiceAvailability($db); break;
    // Submit Client Payment
    case 'submit client payment': echo submitPayment($db); break;
    // Get Occupied Slots
    case 'occupied slots': echo occupiedSlots($db); break;
    // Get All Payment Data
    case 'persons paid': echo paidClient($db); break;
    // Reports Table Admin
    case 'admin reports': echo filterDashboard($db,$_POST['filter'],$_POST['istrue']); break;
    // Reports Table Client
    case 'client reports': echo getPaymentHistory($db); break;
    // Get All Client Payments
    case 'get client payments': echo getClientPayments($db); break;
    // Get this Logged In User Payments
    case 'user payment': echo getUserPayments($db); break;
    // Financial Reports Total
    case 'financial reports': echo financialReports($db); break;
    // Chart Reports
    case 'chart reports': echo chartReport($db); break;
    // Client Dashboard Years
    case 'year selection': echo yearSelection(); break;
    // Check All Service Availability
    case 'service availability': echo serviceAvailability($db); break;
    // Services Selection
    case 'services selection':
        $data = json_decode(services($db));
        echo servicesSelection($data);
    break;
    // Add Service
    case 'add service': echo addService($db); break;
    // Delete Service
    case 'delete service': echo deleteService($db); break;
    // Display Update Service
    case 'display update service': echo displayUpdateService($db); break;
    // Update Service
    case 'update service': echo updateService($db); break;
    // Update Service Image
    case 'update service image': echo updateServiceImage($db); break;
    case 'filter service': echo dashboardFiltering($db); break;
    // Client Rent View
    case 'client view': echo clientRentView($db); break;

}// switch

?>