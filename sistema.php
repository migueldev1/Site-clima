<?php
session_start();
include_once('config.php');

if((!isset($_SESSION['email']) == true) and (!isset($_SESSION['senha']) == true))
{
    unset($_SESSION['email']);
    unset($_SESSION['senha']);
    header('Location: login.php');
    exit();
}

// Função para fazer logout
function logout() {
    $_SESSION = array();
    session_destroy();
}

// Verifica se o botão de logout foi clicado
if(isset($_GET['logout'])) {
    logout();
    header('Location: login.php');
    exit;
}

$logado = $_SESSION['email'];
$nomeUsuario = '';
$cidadeUsuario = '';

// Verifica se o email está definido na sessão
if(isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Consulta o banco de dados para obter o nome do usuário associado ao email
    $sqlNomeUsuario = "SELECT usuario, cidade FROM usuarios WHERE email = '$email'";
    $resultadoNomeUsuario = $conexao->query($sqlNomeUsuario);

    // Verifica se a consulta retornou resultados
    if($resultadoNomeUsuario->num_rows > 0) {
        $row = $resultadoNomeUsuario->fetch_assoc();
        $nomeUsuario = $row['usuario'];
        $cidadeUsuario = $row['cidade'];
    }
}

if(!empty($nomeUsuario)) {
    $logado = $nomeUsuario;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Clima agora!</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/styles.css" />
    <style>
        

        .logout-button {
            background-color: red;
            color: white;
            border: 2px solid red;
            padding: 7px 20px;
            text-decoration: none;
            border-radius: 10px;
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .logout-button:hover {
            background-color: darkred;
        }

        .botao-voltar {
            background-color: red;
            color: white;
            border: 2px solid red;
            padding: 7px 20px;
            text-decoration: none;
            border-radius: 10px;
            position: absolute;
            top: 10px;
            left: 10px;
        }

        .botao-voltar:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
<a href="sistema.php" class="botao-voltar">☚ Voltar</a>
<a href="?logout=true" class="logout-button">Sair</a>
<div class="container">
    <div class="form">
        <h3>Bem vindo <?php echo $logado; ?></h3>
        <h3>Confira o clima de uma cidade:</h3>
        <div class="form-input-container">
            <input type="text" placeholder="Digite o nome da cidade" id="city-input" />
            <button id="search">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </div>
    <div id="user-city-header" class="hide">
      <br>
        <h3>Clima na sua cidade (Definida no cadastro)</h3>
    </div>
    <div id="weather-data" class="hide">
        <h2><i class="fa-solid fa-location-dot"></i> <span id="city"></span> <img id="country" crossorigin="anonymous"></img></h2>
        <p id="temperature"><span></span>&deg;C</p>
        <div id="description-container">
            <p id="description"></p>
            <img id="weather-icon" src="" alt="Condições atuais">
        </div>
        <div id="details-container">
            <p id="umidity">
                <i class="fa-solid fa-droplet"></i>
                <span></span>
            </p>
            <p id="wind">
                <i class="fa-solid fa-wind"></i>
                <span></span>
            </p>
        </div>
    </div>
    <div id="error-message" class="hide">
        <p>Não foi possível encontrar o clima da cidade definida no cadastro. Confira a lista de cidades predefinidas, ou pesquise uma.</p>
        
    </div>
    <div id="loader" class="hide">
        <i class="fa-solid fa-spinner"></i>
    </div>
    <div id="suggestions" class="hide">
        <button id="Paris">Paris</button>
        <button id="saopaulo">São Paulo</button>
        <button id="newyork">New York</button>
        <button id="vancouver">Vancouver</button>
        <button id="pequim">Pequim</button>
        <button id="berlim">Berlim</button>
        <button id="tokyo">Tokyo</button>
        <button id="alaska">Alaska</button>
    </div>
    <br>
</div>
<script>
const apiKey = "e1770d669fd28c05cd483bde24ec27a2";
const apiCountryURL = "https://flagcdn.com/16x12/br.png";
const apiUnsplash = "https://source.unsplash.com/1600x900/?";

const cityInput = document.querySelector("#city-input");
const searchBtn = document.querySelector("#search");

const cityElement = document.querySelector("#city");
const tempElement = document.querySelector("#temperature span");
const descElement = document.querySelector("#description");
const weatherIconElement = document.querySelector("#weather-icon");
const countryElement = document.querySelector("#country");
const umidityElement = document.querySelector("#umidity span");
const windElement = document.querySelector("#wind span");

const weatherContainer = document.querySelector("#weather-data");
const userCityHeader = document.querySelector("#user-city-header");

const errorMessageContainer = document.querySelector("#error-message");
const loader = document.querySelector("#loader");

const suggestionContainer = document.querySelector("#suggestions");
const suggestionButtons = document.querySelectorAll("#suggestions button");

// Cidade cadastrada do usuário
const cidadeUsuario = "<?php echo $cidadeUsuario; ?>";

// Loader
const toggleLoader = () => {
    loader.classList.toggle("hide");
};

const getWeatherData = async (city) => {
    toggleLoader();

    const apiWeatherURL = `https://api.openweathermap.org/data/2.5/weather?q=${city}&units=metric&appid=${apiKey}&lang=pt_br`;

    const res = await fetch(apiWeatherURL);
    const data = await res.json();

    toggleLoader();

    return data;
};

// Tratamento de erro
const showErrorMessage = () => {
    errorMessageContainer.classList.remove("hide");
    suggestionContainer.classList.remove("hide");
};

const hideInformation = () => {
    errorMessageContainer.classList.add("hide");
    weatherContainer.classList.add("hide");
    userCityHeader.classList.add("hide");
    suggestionContainer.classList.add("hide");
};

const showWeatherData = async (city, isUserCity = false) => {
    hideInformation();

    const data = await getWeatherData(city);

    if (data.cod === "404") {
        showErrorMessage();
        return;
    }

    cityElement.innerText = data.name;
    tempElement.innerText = parseInt(data.main.temp);
    descElement.innerText = data.weather[0].description;
    weatherIconElement.setAttribute(
        "src",
        `http://openweathermap.org/img/wn/${data.weather[0].icon}.png`
    );
    countryElement.setAttribute("src", `https://flagcdn.com/16x12/${data.sys.country.toLowerCase()}.png`);
    umidityElement.innerText = `${data.main.humidity}%`;
    windElement.innerText = `${data.wind.speed}km/h`;

    document.body.style.backgroundImage = `url("${apiUnsplash + city}")`;

    if (isUserCity) {
        userCityHeader.classList.remove("hide");
    }

    weatherContainer.classList.remove("hide");
};
const containsSpecialCharacters = (str) => {
            const specialCharacters = /[<>[\]{}()]/;
            return specialCharacters.test(str);
        };

searchBtn.addEventListener("click", async (e) => {
    e.preventDefault();

    const city = cityInput.value;
    // Verifica se a entrada contém caracteres especiais
    if (containsSpecialCharacters(city)) {
        alert("Por favor, evite o uso de caracteres especiais na pesquisa.");
        return;
    }

    showWeatherData(city);
});

cityInput.addEventListener("keyup", (e) => {
    if (e.code === "Enter") {
        const city = e.target.value;
        showWeatherData(city);
    }
});

// Exibir automaticamente o clima da cidade cadastrada
if (cidadeUsuario) {
    showWeatherData(cidadeUsuario, true);
}

// Event listeners para sugestões
suggestionButtons.forEach(button => {
    button.addEventListener("click", () => {
        showWeatherData(button.innerText);
    });
});
</script>
</body>
</html>
