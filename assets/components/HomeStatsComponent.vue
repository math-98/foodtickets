<template>
  <div class="row">
    <div class="col-5 offset-7">
      <div class="input-group mb-3">
        <input type="date" class="form-control" v-model="_start" :max="_end" required>
        <span class="input-group-text">au</span>
        <input type="date" class="form-control" v-model="_end" :min="_start" required>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-10">
      <Line
        :data="chartData"
        :options="chartOptions"
      ></Line>
    </div>
    <div class="col-2">
      <div>
        <div class="bg-dark p-10 text-white text-center">
          <i class="mdi mdi-plus fs-3 font-16"></i>
          <h5 class="mb-0 mt-1">{{ incomeAmount }} €</h5>
          <small class="font-light">gagnés (Revenus)</small>
        </div>
      </div>
      <div class="mt-3">
        <div class="bg-dark p-10 text-white text-center">
          <i class="mdi mdi-cart fs-3 mb-1 font-16"></i>
          <h5 class="mb-0 mt-1">{{ transactionsFiltered.length }}</h5>
          <small class="font-light">transactions</small>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {
  Chart as ChartJS,
  Legend,
  LinearScale,
  LineElement,
  PointElement,
  TimeScale,
  Title,
  Tooltip
} from 'chart.js'
import 'chartjs-adapter-date-fns';
import { Line } from 'vue-chartjs';

import { format, isAfter, isBefore, sub } from 'date-fns';

ChartJS.register(
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    TimeScale,
    Title,
    Tooltip
);

export default {
  data() {
    return {
      start: sub(new Date(), {
        months: 1,
      }),
      end: new Date(),
    }
  },
  computed: {
    _start: {
      get() {
        return format(this.start, 'yyyy-MM-dd');
      },
      set(value) {
        this.start = new Date(value);
      }
    },
    _end: {
      get() {
        return format(this.end, 'yyyy-MM-dd');
      },
      set(value) {
        this.end = new Date(value);
      }
    },
    chartData() {
      const accountsData = {};

      // Transactions
      this.transactionLines.forEach((line) => {
        const account = this.accounts.find(account => account.id === line.accountId);
        const individual_price = account.individual_price ?? 1;

        if (!accountsData[account.id]) {
          accountsData[account.id] = [];
        }

        accountsData[account.id].push({
          date: new Date(line.transaction.date),
          amount: line.amount * individual_price
        });
      });

      // Incomes
      this.incomes.forEach((income) => {
        const contract = this.contracts.find(contract => contract.id === income.contractId);
        const account = this.accounts.find(account => account.id === contract.accountId);
        const individual_price = account.individual_price ?? 1;

        if (!accountsData[account.id]) {
          accountsData[account.id] = [];
        }

        accountsData[account.id].push({
          date: new Date(income.period),
          amount: income.received * individual_price,
        });
      });

      // Sort
      const datasets = [];
      Object.entries(accountsData).forEach(([accountId, entries]) => {
        const account = this.accounts.find(account => account.id === parseInt(accountId));
        let total = 0;
        const accountData = entries.sort((a, b) => {
          return a.date - b.date;
        }).map(entry => {
          total += entry.amount;
          return [entry.date, total];
        });

        datasets.push({
          label: account.name,
          data: accountData.map(([date, amount]) => {
            return {
              x: date,
              y: amount,
            };
          }),
          fill: false,
          borderColor: account.color,
          tension: 0.1,
        });
      });

      return {
        datasets,
      };
    },
    chartOptions() {
      return {
        responsive: true,
        scales: {
          x: {
            min: this.start,
            max: this.end,
            type: 'time',
          }
        },
      };
    },
    incomeAmount() {
      return this.incomes.reduce((total, income) => {
        const period = new Date(income.period);
        if (isBefore(period, this.start) || isAfter(period, this.end)) return total;

        const contract = this.contracts.find(contract => contract.id === income.contractId);
        const account = this.accounts.find(account => account.id === contract.accountId);
        const individual_price = account.individual_price ?? 1;

        return total + (income.received * individual_price);
      }, 0);
    },
    transactionsFiltered() {
      return this.transactions.filter((transaction) => {
        const date = new Date(transaction.date);
        return !isBefore(date, this.start) && !isAfter(date, this.end);
      });
    },
  },
  components: { Line },
  props: {
    accounts: {
      type: Array,
      required: true,
    },
    contracts: {
      type: Array,
      required: true,
    },
    incomes: {
      type: Array,
      required: true,
    },
    transactions: {
      type: Array,
      required: true,
    },
    transactionLines: {
      type: Array,
      required: true,
    },
  },
}
</script>

<style scoped>

</style>