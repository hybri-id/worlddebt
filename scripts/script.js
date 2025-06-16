const debtTableBody = document.getElementById('debtTableBody');
const countryInput = document.getElementById('countryInput');
const lastUpdatedDiv = document.getElementById('lastUpdated');
const headers = document.querySelectorAll('th.sortable');

let currentData = [];
let sortKey = 'country';    // default sort by country
let sortAsc = true;         // default ascending

// Helper to format currency
function formatCurrency(num) {
    return num.toLocaleString('en-US', {style: 'currency', currency: 'USD', maximumFractionDigits: 0});
}

// Helper to format percent
function formatPercent(num, decimals = 2) {
    return num.toFixed(decimals) + '%';
}

// Sort current data by key and order
function sortData() {
    currentData.sort((a, b) => {
        let valA = a[sortKey];
        let valB = b[sortKey];

        if (sortKey === 'debt_percent_gdp') {
            // Make sure values are numbers; fallback to 0 if NaN
            valA = parseFloat(valA) || 0;
            valB = parseFloat(valB) || 0;
            return sortAsc ? valA - valB : valB - valA;
        }
		
		if (sortKey === 'debt') {
			// Make sure values are numbers; fallback to 0 if NaN
			valA = parseFloat(valA) || 0;
			valB = parseFloat(valB) || 0;
			return sortAsc ? valA - valB : valB - valA;
		}
		
        if (typeof valA === 'string') {
            valA = valA.toLowerCase();
            valB = valB.toLowerCase();
            if (valA < valB) return sortAsc ? -1 : 1;
            if (valA > valB) return sortAsc ? 1 : -1;
            return 0;
        }

        valA = parseFloat(valA);
        valB = parseFloat(valB);
        if (isNaN(valA)) valA = 0;
        if (isNaN(valB)) valB = 0;
        return sortAsc ? valA - valB : valB - valA;
    });
}

// Render sorted data into table
function renderDebtDataWithInterest(data) {
    if (data.length === 0) {
        debtTableBody.innerHTML = `<tr><td colspan="3" style="text-align:center;">No matching countries found. <p><a href="import_csv.php">Go to CSV Importer</a></p></td></tr>`;
        return;
    }

    sortData();

    debtTableBody.innerHTML = '';
    data.forEach(item => {
        const tr = document.createElement('tr');
        tr.setAttribute('data-country', item.country);

        const tdCountry = document.createElement('td');
        const link = document.createElement('a');
        link.href = 'country.php?country=' + encodeURIComponent(item.country);
        link.textContent = item.country;
        link.className = 'country-link';
        tdCountry.appendChild(link);
        tr.appendChild(tdCountry);

        const tdDebt = document.createElement('td');
		tdDebt.className = 'debt-value';
		tdDebt.setAttribute('data-base-debt', item.debt);
		tdDebt.setAttribute('data-interest-per-second', item.interest_per_second);
		tdDebt.setAttribute('data-last-updated', item.last_updated_unix);
		tdDebt.textContent = formatCurrency(item.debt);
		tr.appendChild(tdDebt);

        const tdDebtPercentGDP = document.createElement('td');
		const rawPercent = Number(item.debt_percent_gdp);
		tdDebtPercentGDP.setAttribute('data-value', rawPercent);
		tdDebtPercentGDP.textContent = formatPercent(rawPercent);
		tr.appendChild(tdDebtPercentGDP);
		
        debtTableBody.appendChild(tr);
    });
}

// Update sorting UI aria and styles
function updateSortIndicators() {
    headers.forEach(th => {
        if (th.dataset.key === sortKey) {
            th.setAttribute('aria-sort', sortAsc ? 'ascending' : 'descending');
        } else {
            th.setAttribute('aria-sort', 'none');
        }
    });
}

// Event listeners for sorting on header click & keyboard enter
headers.forEach(th => {
    th.addEventListener('click', () => {
        if (sortKey === th.dataset.key) {
            sortAsc = !sortAsc;
        } else {
            sortKey = th.dataset.key;
            sortAsc = true;
        }
        updateSortIndicators();
        renderDebtDataWithInterest(currentData);
    });
    th.addEventListener('keydown', (e) => {
        if (e.key === "Enter" || e.key === " "){
            e.preventDefault();
            th.click();
        }
    });
});

// Fetch data and render
async function fetchDebtData(country = '') {
    try {
        const url = 'fetch_debt.php' + (country ? '?country=' + encodeURIComponent(country) : '');
        const response = await fetch(url, {cache: 'no-store'});
        if (!response.ok) throw new Error('Network response was not ok: ' + response.status);
        const data = await response.json();
        if (data.error) throw new Error(data.error);
        currentData = data;
        updateSortIndicators();
        renderDebtDataWithInterest(currentData);
        updateLastUpdated(currentData);
    } catch (error) {
        debtTableBody.innerHTML = `<tr><td colspan="3" style="color:red; text-align:center;">Failed to load data. <p><a href="import_csv.php">Go to CSV Importer</a></p></td></tr>`;
        lastUpdatedDiv.textContent = '';
        console.error('Error fetching data:', error);
    }
}

function updateLastUpdated(data) {
    if (!data.length) {
        lastUpdatedDiv.textContent = '';
        return;
    }
    const timestamps = data.map(d => d.last_updated_unix);
    const mostRecent = Math.max(...timestamps);
    const dt = new Date(mostRecent * 1000);
    lastUpdatedDiv.textContent = "Data last updated on: " + dt.toLocaleString();
}

function updateDebtsEverySecond() {
    const nowUnix = Math.floor(Date.now() / 1000);
    document.querySelectorAll('#debtTableBody td.debt-value').forEach(td => {
        const baseDebt = parseFloat(td.getAttribute('data-base-debt'));
        const interestPerSecond = parseFloat(td.getAttribute('data-interest-per-second'));
        const lastUpdatedUnix = parseInt(td.getAttribute('data-last-updated'), 10);

        if (isNaN(baseDebt) || isNaN(interestPerSecond) || isNaN(lastUpdatedUnix)) return;

        const elapsed = nowUnix - lastUpdatedUnix;
        const updatedDebt = baseDebt + interestPerSecond * elapsed;

        td.textContent = formatCurrency(updatedDebt);
    });
}

countryInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchDebtData(countryInput.value.trim());
    }, 300);
});

fetchDebtData();
setInterval(updateDebtsEverySecond, 1000);
let debounceTimer = null;
window.addEventListener("DOMContentLoaded", async event => {
  if ('BeforeInstallPromptEvent' in window) {
    showResult("⏳ BeforeInstallPromptEvent supported but not fired yet");
  } else {
    showResult("❌ BeforeInstallPromptEvent NOT supported");    
  }
  document.querySelector("#install").addEventListener("click", installApp);
});

let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
  // Prevents the default mini-infobar or install dialog from appearing on mobile
  //e.preventDefault();
  // Save the event because you’ll need to trigger it later.
  deferredPrompt = e;
  // Show your customized install prompt for your PWA
  document.querySelector("#install").style.display="block";  
  showResult("✅ BeforeInstallPromptEvent fired", true);
  
});

window.addEventListener('appinstalled', (e) => {
  showResult("✅ AppInstalled fired", true);
});

async function installApp() {
  if (deferredPrompt) {
    deferredPrompt.prompt();
	beforeinstallpromptevent.prompt();
    showResult("🆗 Installation Dialog opened");
    // Find out whether the user confirmed the installation or not
    const { outcome } = await deferredPrompt.userChoice;
    // The deferredPrompt can only be used once.
    deferredPrompt = null;
    // Act on the user's choice
    if (outcome === 'accepted') {
      showResult('😀 User accepted the install prompt.', true);
    } else if (outcome === 'dismissed') {
      showResult('😟 User dismissed the install prompt');
    }
    // We hide the install button
    document.querySelector("#install").style.display="none";

  }
}

function showResult(text, append=false) {
  if (append) {
      document.querySelector("output").innerHTML += "<br>" + text;
  } else {
     document.querySelector("output").innerHTML = text;    
  }
}
//Copyright footer current year
var currentYear = new Date().getFullYear();
document.getElementById('currentYear').textContent = currentYear;