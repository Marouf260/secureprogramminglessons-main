<?php
session_start();
include 'includes/db.php';

//Tables aanmaken
include 'includes/userTable.php';
include 'includes/transactionTable.php';

//Controleer of post is geset
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Gebruikersnaam en wachtwoord uit post halen
    $username = $_POST['username'];
    $password = $_POST['password'];

    // kwetsbaar voor SQL injectie
    $sql = "SELECT * FROM user WHERE username = ?";
    $result = $pdo->prepare($sql);
    $result->execute([$username]);
    $user = $result->fetch();

    $nu = time();
    if ($user['isAdmin'] == 1) {
        $_SESSION['isAdmin'] = 1;
    } else {
        $_SESSION['isAdmin'] = 0;
    }

    if ($user && $user['lockout_until'] && strtotime($user['lockout_until']) > $nu) {

        $resterendeTijd = strtotime($user['lockout_until']) - $nu;
        $minuten = ceil($resterendeTijd / 60);
        $error = "Je account is tijdelijk geblokkeerd. Probeer het over " . $minuten . " minuut/minuten weer.";
    }




    // Controleer of er een rij is gevonden
    else if ($result->rowCount() > 0 && password_verify($password, $user['password'])) {

        $stmt = $pdo->prepare("UPDATE user SET failed_attempts = 0, lockout_until = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        // Gebruiker is ingelogd
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['user'] = $user;


        header("location: dashboard.php");
        exit;
    } else {
        // Gebruiker is niet ingelogd
        $error = "Gebruikersnaam of wachtwoord is onjuist.";
        if ($user) {
            $nieuwe_pogingen = $user['failed_attempts'] + 1;

            if ($nieuwe_pogingen >= 3) {

                $blokkeer_tot = date('Y-m-d H:i:s', strtotime('+3 minutes'));
                $stmt = $pdo->prepare("UPDATE user SET failed_attempts = ?, lockout_until = ? WHERE id = ?");
                $stmt->execute([$nieuwe_pogingen, $blokkeer_tot, $user['id']]);

                $error = "Te veel mislukte pogingen. Dit account is voor 3 minuten geblokkeerd.";
            } else {

                $stmt = $pdo->prepare("UPDATE user SET failed_attempts = ? WHERE id = ?");
                $stmt->execute([$nieuwe_pogingen, $user['id']]);

                $error = "Gebruikersnaam of wachtwoord is onjuist. Poging " . $nieuwe_pogingen . " van 3.";
            }
        }
    }


}

?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omanido</title>
    <!-- Voeg Tailwind CSS toe via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto mt-20 p-6 bg-white max-w-sm shadow-md rounded-md">
        <div class="flex justify-center">
            <img src="img/Omanido1.png" alt="Omanido Logo" class="mb-6 w-1/2">
            <!-- Aanpassen van de breedte naar 1/2 van de container -->
        </div>
        <h2 class="text-lg text-center font-bold mb-6">Inloggen bij Omanido</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Fout!</strong>
                <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700">Gebruikersnaam:</label>
                <input type="text" id="username" name="username"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700">Wachtwoord:</label>
                <input type="password" id="password" name="password"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    required>
            </div>
            <input type="submit" value="Inloggen"
                class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 focus:outline-none focus:shadow-outline">
        </form>
        <a href="register.php" class="block text-center text-sm text-blue-600 hover:underline mt-4">Nog geen account?
            Registreer hier</a>
    </div>

    <div class="mt-4 p-2 border border-gray-300 rounded">
        <label class="block text-sm font-medium text-gray-700">Uitgevoerde SQL-query:</label>
        <textarea readonly class="mt-1 block w-full border rounded-md py-2 px-3 resize-none" rows="4"><?php //als $sql bestaat geef $sql, anders geef aan dat deze nog niet is ingevuld
        if (isset($sql)) {
            echo htmlspecialchars($sql);
        } else {
            echo "Log in om je SQL query te zien";
        }
        ?></textarea>
    </div>


</body>

</html>