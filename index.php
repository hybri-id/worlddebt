<?php
// index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="description" content="Access up-to-date information on the debt status of countries worldwide. Our real-time tracking provides insights into global economic conditions and trends.">
<meta name="keywords" content="World, Debt, Country, Statistics">
<title>World Debt Dashboard</title>
<link rel="icon" type="image/png" href="img/favicon-96x96.png" sizes="96x96" />
<link rel="icon" type="image/svg+xml" href="img/favicon.svg" />
<link rel="shortcut icon" href="img/favicon.ico" />
<link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png" />
<meta name="apple-mobile-web-app-title" content="World Debt" />
<link rel="manifest" href="site.webmanifest" />
<link rel="stylesheet" href="css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
</head>
<body>
<main>
<h1>World Debt Dashboard</h1>
<div class="filter">
    <input type="text" id="countryInput" placeholder="Filter by country (e.g. USA)" autocomplete="off" />
</div>

<table aria-label="World debts table" role="table">
    <thead>
        <tr>
            <th scope="col" class="sortable" data-key="country" tabindex="0" role="button" aria-sort="none" aria-label="Sort by Country">Country</th>
            <th scope="col" class="sortable" data-key="debt" tabindex="0" role="button" aria-sort="none" aria-label="Sort by Debt (USD)">Debt (USD)</th>
            <th scope="col" class="sortable" data-key="debt_percent_gdp" tabindex="0" role="button" aria-sort="none" aria-label="Sort by Debt as % of GDP">Debt as % of GDP</th>
        </tr>
    </thead>
	<div style="text-align:center; margin-bottom: 15px;">
		<button id="exportCSVBtn" style="padding:8px 16px; font-size:16px;">Export Data to CSV</button>
	</div>
	<script>
	document.getElementById('exportCSVBtn').addEventListener('click', () => {
		window.location.href = 'export_csv.php';
	});
	</script>
    <tbody id="debtTableBody">
        <tr><td colspan="3" style="text-align:center;">Loading...</td></tr>
    </tbody>
</table>
<div id="lastUpdated"></div>
<div class="installInstructions">
	<h1>
	Install this app
	</h1>
	<p class="instructions">
	Load this PWA in a browser supporting beforeinstallprompt.
	</p>
	<section class="toolbar">
	<button id="install">Install this App</button>
	</section>
	<output></output>
</div>
</main>
<footer>
    <p>Copyright &copy; 2023-<span id="currentYear"></span> <a href="javascript:void(0)">Kylo_Rey</a>. All Rights Reserved.</p>
</footer>
<script defer src="scripts/script.min.js"></script>
</body>
</html>