<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Models/Payment.php';

class PaymentController {
    private $paymentModel;

    public function __construct() {
        $this->paymentModel = new Payment();
    }

    // 1. Initialize Paystack Payment (Redirects user to Paystack Gateway)
    public function initializePaystack($email, $amount, $enrollment_id, $user_id) {
        $url = "https://api.paystack.co/transaction/initialize";
        $reference = uniqid('MSV-PAY-'); // Generate unique transaction reference
        
        $fields = [
            'email' => $email,
            'amount' => $amount * 100, // Paystack works in kobo (multiply by 100)
            'reference' => $reference,
            'callback_url' => BASE_URL . "verify_payment.php" // Where Paystack redirects after payment
        ];

        $fields_string = http_build_query($fields);

        // cURL setup for Paystack API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
            "Cache-Control: no-cache",
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        $response = json_decode($result, true);

        if ($response['status']) {
            // Save the pending payment attempt in our database before redirecting
            $this->paymentModel->createPayment([
                'enrollment_id' => $enrollment_id,
                'user_id' => $user_id,
                'amount' => $amount,
                'reference' => $reference,
                'method' => 'paystack',
                'status' => 'pending'
            ]);
            
            // Return the Paystack hosted checkout URL to redirect the user
            return ['status' => 'success', 'authorization_url' => $response['data']['authorization_url']];
        } else {
            return ['status' => 'error', 'message' => $response['message']];
        }
    }

    // 2. Verify Paystack Payment (Called via Webhook or Callback page)
    public function verifyPaystack($reference) {
        $url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
            "Cache-Control: no-cache",
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        $response = json_decode($result, true);
        
        // If Paystack confirms the payment was successful
        if ($response['status'] && $response['data']['status'] === 'success') {
            // Update database status to approved (this also activates the enrollment in the model)
            $this->paymentModel->updatePaymentStatus($reference, 'approved');
            return true;
        }
        return false;
    }

    // 3. Handle Offline Teller Upload (Saves image and flags for Admin review)
    public function uploadTeller($file, $amount, $enrollment_id, $user_id) {
        $reference = uniqid('OFF-TEL-');
        
        // Basic Security Validation
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed_extensions)) {
            return ['status' => 'error', 'message' => 'Invalid file format. Only JPG, PNG, and PDF are allowed.'];
        }

        // Limit size to 5MB
        if ($file['size'] > 5000000) { 
            return ['status' => 'error', 'message' => 'File size must not exceed 5MB.'];
        }

        // Generate secure random filename
        $filename = $reference . '_' . time() . '.' . $ext;
        $destination = TELLER_DIR . $filename;

        // Move the uploaded file to the tellers directory
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Create pending database record
            $this->paymentModel->createPayment([
                'enrollment_id' => $enrollment_id,
                'user_id' => $user_id,
                'amount' => $amount,
                'reference' => $reference,
                'method' => 'offline_teller',
                'teller_path' => $filename,
                'status' => 'pending'
            ]);
            return ['status' => 'success', 'message' => 'Teller uploaded successfully. It is now pending admin approval.'];
        }
        return ['status' => 'error', 'message' => 'System failed to save the uploaded image. Please try again.'];
    }

    // 4. Approve Offline Teller (For Head of Admin/Accounts)
    public function approveTeller($reference, $admin_id) {
        // In a real application, ensure the user requesting this is actually Role ID 1 or 2
        return $this->paymentModel->updatePaymentStatus($reference, 'approved', $admin_id);
    }
}
