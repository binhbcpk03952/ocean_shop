<script setup>
import { ref, onMounted } from "vue";
import api from "../../axios";
import { Line, Bar, Pie } from "vue-chartjs";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    BarElement,
    PointElement,
    CategoryScale,
    LinearScale,
    ArcElement,
    Filler,
} from "chart.js";

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    BarElement,
    PointElement,
    CategoryScale,
    LinearScale,
    ArcElement,
    Filler
);
const year = ref(new Date().getFullYear());
const data = ref(null);
const loading = ref(true);
const error = ref(null);

async function loadReports() {
    loading.value = true;
    try {
        const res = await api.get(`/reports?year=${year.value}`);
        data.value = res.data;
    } catch (err) {
        error.value = "Không thể tải dữ liệu báo cáo.";
    } finally {
        loading.value = false;
    }
}

onMounted(loadReports);
</script>

<template>
    <div class="container mt-4">
        <h4 class="fw-bold text-primary mb-4">
            <i class="bi bi-bar-chart-fill me-2"></i>Báo cáo doanh thu {{ year }}
        </h4>

        <div class="d-flex align-items-center mb-4">
            <label class="fw-semibold me-2">Chọn năm:</label>
            <input type="number" v-model="year" min="2020" max="2100" class="form-control w-auto" />
            <button class="btn btn-outline-primary ms-2" @click="loadReports">
                Cập nhật
            </button>
        </div>

        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <p>Đang tải dữ liệu...</p>
        </div>

        <div v-else-if="error" class="alert alert-danger text-center">{{ error }}</div>

        <div v-else>
            <!-- === 1. Doanh thu theo tháng === -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-success mb-3">
                        <i class="bi bi-graph-up me-2"></i>Doanh thu theo tháng
                    </h5>
                    <Line v-if="data && data.monthlyRevenue && data.monthlyRevenue.length" :data="{
                        labels: data.monthlyRevenue.map(m => 'Tháng ' + m.month),
                        datasets: [{
                            label: 'Doanh thu (₫)',
                            data: data.monthlyRevenue.map(m => Number(m.total_revenue)),
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13,110,253,0.2)',
                            tension: 0.3,
                            fill: true
                        }]
                    }" :options="{ responsive: true }" />

                </div>
            </div>

            <!-- === 2. Doanh thu theo phương thức thanh toán === -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-info mb-3">
                        <i class="bi bi-credit-card me-2"></i>Theo phương thức thanh toán
                    </h5>
                    <div class="w-50">
                        <Pie v-if="data.paymentRevenue" :data="{
                            labels: data.paymentRevenue.map(p => p.payment_method.toUpperCase()),
                            datasets: [{
                                label: 'Doanh thu (₫)',
                                data: data.paymentRevenue.map(p => Number(p.total_revenue)),
                                backgroundColor: ['#0d6efd', '#198754', '#dc3545', '#ffc107', '#6610f2'],
                            }]
                        }" />
                    </div>
                </div>
            </div>

            <!-- === 3. Top sản phẩm bán chạy === -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-warning mb-3">
                        <i class="bi bi-trophy-fill me-2"></i>Top sản phẩm bán chạy
                    </h5>
                    <Bar v-if="data.topProducts" :data="{
                        labels: data.topProducts.map(p => p.product_name),
                        datasets: [{
                            label: 'Số lượng bán',
                            data: data.topProducts.map(p => Number(p.total_sold)),
                            backgroundColor: '#ffc107'
                        }]
                    }" :options="{ responsive: true, plugins: { legend: { display: false } } }" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.card {
    border: none;
    border-radius: 1rem;
}

.card-title {
    font-weight: 600;
}
</style>
