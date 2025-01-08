<?php
session_start();
require_once '../../config/config.php';
require_once '../../models/UserModel.php';

// if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location:../../public/index.php');
    exit;
}

// $test_db = new Database('bank');
// $pdo = $test_db->getConnection();
// $hashedp = password_hash("20JvAt02", PASSWORD_DEFAULT);
// $test_username = 'administrator';
// $test_email = 'admin@test.com';
// $role = 'admin';
// $aze = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
// $aze->execute([$test_username, $test_email, $hashedp, $role]);
// if ($aze->fetch()) {
//     echo "user created \n";
// }

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $db = new Database('bank');
        $conn = $db->getConnection();

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);

        if ($user && password_verify($password, $user->password)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['role'] = $user->role;
            header('Location:../../public/index.php');
            exit;
        } else {
            $error = "Invalid email or password";
        }
    } catch (Exception $e) {
        $error = "An error occurred. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Account Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <style>
        :root {
            --primary-orange: #FF7043;
            --primary-purple: #9C27B0;
            --light-purple: #BA68C8;
        }

        .forms-wrapper {
            position: relative;
            min-height: 480px;
            perspective: 1000px;
        }

        .form-container {
            opacity: 1;
            transition: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: absolute;
            width: 100%;
            transform-origin: center center -50px;
            backface-visibility: hidden;
            transform: rotateY(0) translateZ(0);
        }

        .form-container.hidden {
            opacity: 0;
            transform: rotateY(180deg) translateZ(100px);
            pointer-events: none;
        }

        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-field {
            width: 100%;
            padding: 1rem 1.2rem;
            background: rgba(156, 39, 176, 0.1);
            border: 2px solid rgba(186, 104, 200, 0.2);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .input-field:focus {
            background: rgba(156, 39, 176, 0.2);
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 4px rgba(255, 112, 67, 0.1), 0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .input-label {
            position: absolute;
            left: 1rem;
            top: 1rem;
            padding: 0 0.5rem;
            color: #E1BEE7;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
            background: linear-gradient(180deg, transparent 50%, rgba(156, 39, 176, 0.1) 50%);
        }

        .input-field:focus+.input-label,
        .input-field:not(:placeholder-shown)+.input-label {
            transform: translateY(-2.4rem) scale(0.85);
            color: var(--primary-orange);
            background: linear-gradient(180deg, #4A148C 50%, rgba(156, 39, 176, 0.1) 50%);
        }

        .input-field::placeholder {
            color: transparent;
        }

        .btn-primary {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            z-index: 1;
            background: linear-gradient(135deg, var(--primary-orange) 0%, #F4511E 100%);
        }

        .btn-primary:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%) scale(0);
            transition: transform 0.6s ease;
            z-index: -1;
        }

        .btn-primary:hover:before {
            transform: translate(-50%, -50%) scale(1);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 112, 67, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            position: relative;
            transition: all 0.3s ease;
            color: var(--primary-orange);
        }

        .btn-secondary:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary-orange);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .btn-secondary:hover:after {
            width: 100%;
        }

        .bank-icon {
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            background: linear-gradient(135deg, var(--primary-orange) 0%, #F4511E 100%);
        }

        .bank-icon:hover {
            transform: scale(1.1) rotate(5deg);
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-purple-700 to-purple-900 min-h-screen flex items-center justify-center p-4">
    <div class="bg-purple-900/90 backdrop-blur-sm p-8 rounded-xl shadow-2xl w-full max-w-4xl flex flex-col md:flex-row gap-8">
        <!-- Left Side - Bank Icon and Welcome Message -->
        <div class="flex-1 flex flex-col items-center justify-center">
            <div class="bank-icon bg-orange-400 rounded-full p-8 w-32 h-32 mx-auto mb-8 shadow-lg floating">
                <div class="text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11m16-11v11m-8-11v11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-white mb-4 text-center">Let's set up your bank account!</h1>
            <p class="text-indigo-200 text-center">Your money is safe with us! Don't worry it should take a couple of minutes.</p>
        </div>

        <!-- Right Side - Forms Container -->
        <div class="flex-1">
            <div class="forms-wrapper">
                <!-- Login Form -->
                <div id="loginForm" class="form-container pt-20">
                    <h2 class="text-2xl font-semibold text-white mb-6 ">Sign In</h2>
                    <!-- <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="space-y-6"> -->
                    <form action="login.php" method="POST" class="space-y-6">
                        <div class="input-group">
                            <input type="email" id="login-email" name="email"
                                class="input-field" placeholder=" ">
                            <label for="login-email" class="input-label">
                                Email Address
                            </label>
                        </div>
                        <div class="input-group">
                            <input type="password" id="login-password" name="password"
                                class="input-field" placeholder=" ">
                            <label for="login-password" class="input-label">
                                Password
                            </label>
                        </div>
                        <button type="submit" name="login"
                            class="btn-primary w-full py-3 px-6 rounded-lg text-white font-semibold">
                            Sign In
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add floating animation to bank icon
        document.addEventListener('DOMContentLoaded', () => {
            gsap.to('.bank-icon', {
                y: -10,
                duration: 1.5,
                repeat: -1,
                yoyo: true,
                ease: "power1.inOut"
            });
        });
    </script>
</body>

</html>