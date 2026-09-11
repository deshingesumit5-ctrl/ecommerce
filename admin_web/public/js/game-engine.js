/**
 * Fun 2 Win Game Engine
 * Handles live game state synchronization, betting timer countdown, and placing bets.
 */
class GameEngine {
    constructor(config) {
        this.roomId = config.roomId;
        this.stateUrl = config.stateUrl;
        this.betUrl = config.betUrl;
        this.cancelUrlBase = config.cancelUrlBase;
        this.csrfToken = config.csrfToken;
        this.selectedChip = config.defaultChip || 100;
        this.cancellationDuration = config.cancellationDuration || 30;

        this.currentRoundId = null;
        this.currentStatus = null;
        this.remainingSeconds = 0;
        this.timerInterval = null;
        this.pollInterval = null;
        this.resultModalShownForRound = null;

        this.init();
    }

    init() {
        this.bindEvents();
        this.fetchState();
        this.startPolling();
    }

    bindEvents() {
        // Chip selection
        document.querySelectorAll('.chip-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.chip-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                this.selectedChip = parseInt(btn.dataset.value, 10);
                this.updateBetButtonLabels();
            });
        });

    }

    updateBetButtonLabels() {
        const andarAmountSpan = document.getElementById('andar-current-chip');
        const baharAmountSpan = document.getElementById('bahar-current-chip');
        if (andarAmountSpan) andarAmountSpan.textContent = this.selectedChip.toLocaleString();
        if (baharAmountSpan) baharAmountSpan.textContent = this.selectedChip.toLocaleString();
    }

    startPolling() {
        this.pollInterval = setInterval(() => {
            this.fetchState();
        }, 1500);
    }

    async fetchState() {
        try {
            const res = await fetch(this.stateUrl, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;

            const data = await res.json();
            this.renderState(data);
        } catch (err) {
            console.error('Error fetching game state:', err);
        }
    }

    updateWalletBalance(balance) {
        if (balance === undefined || balance === null) return;
        const balanceEls = document.querySelectorAll('.user-wallet-balance');
        balanceEls.forEach(el => {
            el.textContent = Math.floor(Number(balance)).toLocaleString('en-US');
        });
    }

    renderState(data) {
        // 1. Update Wallet Balance
        if (data.wallet_balance !== undefined) {
            this.updateWalletBalance(data.wallet_balance);
        }
        if (data.cancellation_duration !== undefined) {
            this.cancellationDuration = data.cancellation_duration;
        }

        // 2. Round Info
        const roundNumEl = document.getElementById('round-number-display');
        if (roundNumEl) roundNumEl.textContent = `#${data.round_number}`;

        const roundStatusBadge = document.getElementById('round-status-badge');
        if (roundStatusBadge) {
            const statusMap = {
                'open': { text: 'WAITING FOR DEAL', class: 'bg-slate-700 text-slate-200' },
                'betting_open': { text: 'BETTING OPEN', class: 'bg-emerald-600 text-white animate-pulse' },
                'betting_closed': { text: 'BETTING CLOSED', class: 'bg-amber-600 text-white' },
                'result_declared': { text: 'RESULT DECLARED', class: 'bg-indigo-600 text-white' },
                'round_closed': { text: 'ROUND CLOSED', class: 'bg-slate-800 text-slate-400' },
            };
            const s = statusMap[data.round_status] || { text: data.round_status, class: 'bg-slate-700 text-slate-200' };
            roundStatusBadge.textContent = s.text;
            roundStatusBadge.className = `px-3 py-1 text-xs font-bold rounded-full ${s.class}`;
        }

        // 3. First Card Render
        this.renderFirstCard(data.first_card);

        // 4. Timer Handling
        const isBettingOpen = (data.round_status === 'betting_open');
        this.updateBettingControls(isBettingOpen);
        this.updateTimer(data.remaining_seconds, data.betting_duration || 30, isBettingOpen);

        // 5. Total volume odds bar
        this.updateVolumeBar(data.total_andar || 0, data.total_bahar || 0);

        // 6. User Bets List & Cancel buttons
        this.renderUserBets(data.user_bets || []);

        // 7. Dynamic Result Celebration
        if (data.round_status === 'result_declared' && data.winning_side !== 'none') {
            if (this.resultModalShownForRound !== data.round_id) {
                this.resultModalShownForRound = data.round_id;
                this.showResultOverlay(data);
            }
        }

        // 8. Recent round badges
        if (data.recent_history) {
            this.renderRecentHistory(data.recent_history);
        }

        this.currentRoundId = data.round_id;
        this.currentStatus = data.round_status;
    }

    renderFirstCard(cardCode) {
        const container = document.getElementById('first-card-container');
        if (!container) return;

        if (!cardCode) {
            container.innerHTML = `
                <div class="card-empty">
                    <div class="text-center">
                        <span class="text-2xl block mb-1">🃏</span>
                        <span class="text-xs uppercase font-medium">Waiting Deal</span>
                    </div>
                </div>`;
            return;
        }

        // Parse card code e.g. "king_hearts", "2_spades", "ace_diamonds"
        const parts = cardCode.split('_');
        const val = parts[0] ? parts[0].toUpperCase() : '?';
        const suit = parts[1] || 'spades';

        const suitSymbols = {
            'spades': '♠',
            'hearts': '♥',
            'diamonds': '♦',
            'clubs': '♣'
        };

        const isRed = (suit === 'hearts' || suit === 'diamonds');
        const colorClass = isRed ? 'text-red-600' : 'text-slate-900';
        const symbol = suitSymbols[suit] || '♠';

        const shortVal = val === 'JACK' ? 'J' : (val === 'QUEEN' ? 'Q' : (val === 'KING' ? 'K' : (val === 'ACE' ? 'A' : val)));

        container.innerHTML = `
            <div class="card-slot ${colorClass}">
                <div class="flex justify-between items-start font-bold text-lg leading-none">
                    <span>${shortVal}</span>
                    <span class="text-base">${symbol}</span>
                </div>
                <div class="text-4xl text-center my-auto">${symbol}</div>
                <div class="flex justify-between items-end font-bold text-lg leading-none rotate-180">
                    <span>${shortVal}</span>
                    <span class="text-base">${symbol}</span>
                </div>
            </div>`;
    }

    updateTimer(seconds, totalDuration, isBettingOpen) {
        const timerText = document.getElementById('betting-timer-seconds');
        const timerBar = document.getElementById('betting-timer-progress');
        const timerRing = document.getElementById('betting-timer-ring');

        if (!isBettingOpen || seconds <= 0) {
            if (timerText) timerText.textContent = '00';
            if (timerBar) timerBar.style.width = '0%';
            if (timerRing) timerRing.style.strokeDashoffset = '283';
            return;
        }

        const formatted = seconds < 10 ? `0${seconds}` : `${seconds}`;
        if (timerText) timerText.textContent = formatted;

        const percentage = (seconds / totalDuration) * 100;
        if (timerBar) timerBar.style.width = `${percentage}%`;

        if (timerRing) {
            const circumference = 283; // 2 * PI * 45
            const offset = circumference - (percentage / 100) * circumference;
            timerRing.style.strokeDashoffset = offset.toString();
        }
    }

    updateBettingControls(isOpen) {
        const btnAndar = document.getElementById('btn-bet-andar');
        const btnBahar = document.getElementById('btn-bet-bahar');
        const overlay = document.getElementById('betting-disabled-overlay');

        if (btnAndar) btnAndar.disabled = !isOpen;
        if (btnBahar) btnBahar.disabled = !isOpen;

        if (overlay) {
            overlay.style.display = isOpen ? 'none' : 'flex';
        }
    }

    updateVolumeBar(andarVol, baharVol) {
        const total = andarVol + baharVol;
        const andarPct = total > 0 ? Math.round((andarVol / total) * 100) : 50;
        const baharPct = 100 - andarPct;

        const andarBar = document.getElementById('bar-andar-vol');
        const baharBar = document.getElementById('bar-bahar-vol');
        const andarText = document.getElementById('text-andar-vol');
        const baharText = document.getElementById('text-bahar-vol');

        if (andarBar) andarBar.style.width = `${andarPct}%`;
        if (baharBar) baharBar.style.width = `${baharPct}%`;
        if (andarText) andarText.textContent = `${andarVol.toLocaleString()} pts (${andarPct}%)`;
        if (baharText) baharText.textContent = `${baharVol.toLocaleString()} pts (${baharPct}%)`;
    }

    renderUserBets(bets) {
        const list = document.getElementById('my-bets-list');
        if (!list) return;

        if (bets.length === 0) {
            list.innerHTML = `<div class="text-center text-slate-500 py-4 text-xs">No bets placed in this round yet.</div>`;
            return;
        }

        list.innerHTML = bets.map(bet => {
            const isAndar = bet.selection === 'andar';
            const badgeClass = isAndar ? 'badge-andar' : 'badge-bahar';
            const statusColors = {
                'active': 'text-amber-400',
                'won': 'text-emerald-400 font-bold',
                'lost': 'text-slate-500 line-through',
                'cancelled': 'text-red-400 italic',
            };

            let cancelHtml = '';
            if (bet.can_cancel) {
                const remSec = (bet.remaining_cancel_seconds !== undefined && bet.remaining_cancel_seconds !== null) ? bet.remaining_cancel_seconds : this.cancellationDuration;
                cancelHtml = `
                    <button class="btn-cancel-bet px-2.5 py-1 text-xs bg-red-600 hover:bg-red-700 text-white rounded font-semibold transition" 
                            data-bet-id="${bet.id}">
                        Cancel (${remSec}s)
                    </button>`;
            }

            return `
                <div class="flex items-center justify-between p-2.5 bg-slate-900/60 rounded-lg border border-slate-800 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded font-bold uppercase ${badgeClass}">${bet.selection}</span>
                        <span class="text-white font-semibold">${bet.amount.toLocaleString()} pts</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="${statusColors[bet.status] || 'text-slate-300'} uppercase">${bet.status}</span>
                        ${cancelHtml}
                    </div>
                </div>
            `;
        }).join('');

        // Bind cancel buttons
        list.querySelectorAll('.btn-cancel-bet').forEach(btn => {
            btn.onclick = () => this.cancelBet(btn.dataset.betId);
        });
    }

    renderRecentHistory(rounds) {
        const container = document.getElementById('recent-rounds-pills');
        if (!container) return;

        container.innerHTML = rounds.map(r => {
            const isAndar = r.winning_side === 'andar';
            const bgClass = isAndar ? 'bg-indigo-600 text-white' : 'bg-red-600 text-white';
            const label = isAndar ? 'A' : (r.winning_side === 'bahar' ? 'B' : '-');
            return `<div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shadow ${bgClass}" title="Round #${r.round_number}: ${r.winning_side.toUpperCase()}">${label}</div>`;
        }).join('');
    }

    async placeBet(selection) {
        try {
            const res = await fetch(this.betUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    selection: selection,
                    amount: this.selectedChip,
                })
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                window.showToast(data.message || 'Failed to place bet.', 'error');
                return;
            }

            window.showToast(data.message, 'success');
            if (typeof window.onBetPlacedSuccess === 'function') {
                window.onBetPlacedSuccess(data);
            }
            this.fetchState();
        } catch (err) {
            console.error('Bet error:', err);
            window.showToast('Network error while placing bet.', 'error');
        }
    }

    async cancelBet(betId) {
        try {
            const url = `${this.cancelUrlBase}/${betId}/cancel`;
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                }
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                window.showToast(data.message || 'Cannot cancel bet.', 'error');
                return;
            }

            if (data.wallet_balance !== undefined) {
                this.updateWalletBalance(data.wallet_balance);
            }
            window.showToast(data.message || 'Bet cancelled successfully', 'success');
            if (typeof window.onBetCancelledSuccess === 'function') {
                window.onBetCancelledSuccess(data);
            }
            this.fetchState();
        } catch (err) {
            console.error('Cancel error:', err);
            window.showToast('Network error cancelling bet.', 'error');
        }
    }

    showResultOverlay(data) {
        const winningSide = data.winning_side;
        const userBets = data.user_bets || [];
        const winningBets = userBets.filter(b => b.selection === winningSide && b.status !== 'cancelled');
        const hasWon = winningBets.length > 0;
        const totalWonPayout = winningBets.reduce((acc, b) => acc + (b.amount * 2), 0);

        let modal = document.getElementById('round-result-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'round-result-modal';
            modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md';
            document.body.appendChild(modal);
        }

        const isAndar = winningSide === 'andar';
        const winTitle = isAndar ? 'ROYAL ANDAR WON!' : 'ROYAL BAHAR WON!';
        const headerColor = isAndar ? 'from-indigo-600 to-indigo-900 border-indigo-400' : 'from-red-600 to-red-900 border-red-400';

        let userResultHtml = '';
        if (userBets.length > 0) {
            if (hasWon) {
                userResultHtml = `
                    <div class="p-4 bg-emerald-950/80 border border-emerald-500 rounded-xl my-4 text-center">
                        <span class="text-3xl block mb-1">🎉</span>
                        <h4 class="text-emerald-300 font-bold text-lg uppercase">YOU WON!</h4>
                        <p class="text-emerald-100 text-sm mt-1">1:1 Return Credited: <strong class="text-xl text-yellow-300">+${totalWonPayout.toLocaleString()} pts</strong></p>
                    </div>`;
            } else {
                userResultHtml = `
                    <div class="p-4 bg-slate-900/80 border border-slate-700 rounded-xl my-4 text-center">
                        <h4 class="text-slate-300 font-bold text-md uppercase">BET LOST</h4>
                        <p class="text-slate-400 text-xs mt-1">Better luck in the next round!</p>
                    </div>`;
            }
        } else {
            userResultHtml = `
                <div class="p-3 bg-slate-900/60 rounded-lg my-4 text-center text-xs text-slate-400">
                    You did not place any bets in this round.
                </div>`;
        }

        modal.innerHTML = `
            <div class="glass-panel max-w-md w-full p-6 text-center border-2 ${headerColor} shadow-2xl animate-bounce-short">
                <span class="text-xs uppercase tracking-widest text-slate-400">Round #${data.round_number} Result</span>
                <h2 class="text-2xl md:text-3xl font-royal font-black text-white mt-1 mb-2">${winTitle}</h2>
                <div class="inline-block px-4 py-1.5 rounded-full font-bold text-sm uppercase ${isAndar ? 'badge-andar' : 'badge-bahar'} mb-2">
                    Winner: ${winningSide.toUpperCase()}
                </div>
                ${userResultHtml}
                <div class="text-xs text-slate-400 mb-4">Updated Balance: <strong class="text-white">${Math.floor(Number(data.wallet_balance)).toLocaleString()} pts</strong></div>
                <button onclick="document.getElementById('round-result-modal').remove()" class="btn-gold w-full py-2.5 text-sm uppercase">Continue Playing</button>
            </div>
        `;
    }
}

window.GameEngine = GameEngine;
