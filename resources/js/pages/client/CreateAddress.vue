<script setup>
import { reactive, computed, watch, onMounted } from 'vue';
import api from '../../axios';

const props = defineProps({
    openModal: Boolean,
    mode: {
        type: String,
        default: 'create'
    }
})
const emit = defineEmits(['close', 'save'])
const handleCloseModal = () => {
    emit('close')
}

const form = reactive({
    fullName: '',
    phone: '',
    province: '',
    district: '',
    ward: '',
    street: '',
    type: 'home', // 'home' | 'office'
    isDefault: false
});

const formErrors = reactive({});

const validateField = (field, value) => {
    switch (field) {
        case 'fullName':
            return value.trim() ? '' : 'Vui lòng nhập họ và tên';
        case 'phone':
            const phoneRegex = /^[0-9]{10,11}$/;
            if (!value.trim()) return 'Vui lòng nhập số điện thoại';
            if (!phoneRegex.test(value)) return 'Số điện thoại không hợp lệ';
            return '';
        case 'address':
            return value.trim() ? '' : 'Vui lòng nhập địa chỉ chi tiết';
        case 'province':
            return value.trim() ? '' : 'Vui lòng nhập tỉnh/thành phố';
        case 'district':
            return value.trim() ? '' : 'Vui lòng nhập quận/huyện';
        case 'ward':
            return value.trim() ? '' : 'Vui lòng nhập phường/xã';
        case 'street':
            return value.trim() ? '' : 'Vui lòng nhập tên đường, số nhà';
        default:
            return '';
    }
};

const validateForm = () => {
    let isValid = true;
    Object.keys(form).forEach((field) => {
        const error = validateField(field, form[field]);
        formErrors[field] = error;
        if (error) isValid = false;
    });
    return isValid;
};

const resetForm = () => {
    Object.keys(formErrors).forEach((key) => (formErrors[key] = ''));
};
const address = reactive({
    provinces: [],
    districts: [],
    wards: []
})
const idAddressed = reactive({
    idProvince: '',
    idDistrict: '',
    idWard: '',
})
const fetchProinces = async () => {
    try {
        const res = await api.get('/address/provinces');
        // console.log(res.data);
        address.provinces = res.data.data;
    } catch (err) {
        console.log("Lỗi khi gọi API: ", err)
    }
}
const fetchDistricts = async (provinceId) => {
    try {
        const res = await api.get(`/address/districts/${provinceId}`);
        // console.log(res.data);
        address.districts = res.data.data;
    } catch (err) {
        console.log("Lỗi khi gọi API: ", err)
    }
}
const fetchWards = async (districtId) => {
    try {
        const res = await api.get(`/address/wards/${districtId}`);
        // console.log(res.data);
        address.wards = res.data.data;
    } catch (err) {
        console.log("Lỗi khi gọi API: ", err)
    }
}
const fullNameProvince = computed(() => {
    const province = address.provinces.find(pr => pr.id == idAddressed.idProvince);
    return province ? province.name : '';
})
const fullNameDistrict = computed(() => {
    const district = address.districts.find(dt => dt.id == idAddressed.idDistrict);
    return district ? district.full_name : '';
})
const fullNameWard = computed(() => {
    const ward = address.wards.find(wd => wd.id == idAddressed.idWard);
    return ward ? ward.full_name : '';
})

watch(() => idAddressed.idProvince, (newProvince) => {
    form.district = '';
    form.ward = '';
    address.districts = [];
    address.wards = [];
    if (newProvince) {
        fetchDistricts(newProvince);
        form.province = fullNameProvince.value;
    }
});
watch(() => idAddressed.idDistrict, (newDistrict) => {
    form.ward = '';
    address.wards = [];
    if (newDistrict) {
        fetchWards(newDistrict);
        form.district = fullNameDistrict.value;
    }
});
watch(() => idAddressed.idWard, (newWard) => {
    if (newWard) {
        form.ward = fullNameWard.value;
    }
});




onMounted(() => {
    fetchProinces();
    if (idAddressed.idProvince) {
        fetchDistricts(form.province);
    }
    if (idAddressed.idDistrict) {
        fetchWards(form.district);
    }
}
)



const handleSubmit = async () => {
    // Gom dữ liệu đầy đủ text để gửi về Backend

    if (!validateForm()) {
        console.log("Form validation failed:", formErrors);
        return;
    }
    console.log(form);
    try {
        const res = await api.post('/addresses', form)
        if (res.status === 201) {
            emit('save')
            alert('Thêm địa chỉ thành công');
        }
    } catch (err) {
        console.log('Loi khi goi API: ', err);
    }

};
</script>
<template>
    <div v-if="props.openModal" class="fade show modal" tabindex="-1"
        style="display: block; background-color: rgba(0,0,0,0.5)" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header -->
                <div class="modal-header bg-gradient border-0">
                    <div>
                        <div class="modal-title fw-bold color-main mb-0 fs-5">
                            Thêm địa chỉ
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-black" @click="handleCloseModal"></button>
                </div>
                <hr class="m-0">
                <div class="modal-body px-4 pb-4">
                    <form @submit.prevent="handleSubmit">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input v-model="form.fullName" type="text" class="form-control rounded-3 small"
                                        id="floatingName" placeholder="Họ và tên"
                                        :class="{ 'is-invalid': formErrors.fullName }">
                                    <label for="floatingName " class="small">Họ và tên người nhận</label>
                                    <div class="invalid-feedback">{{ formErrors.fullName }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input v-model="form.phone" type="tel" class="form-control rounded-3 small"
                                        id="floatingPhone" placeholder="Số điện thoại"
                                        :class="{ 'is-invalid': formErrors.phone }">
                                    <label for="floatingPhone" class="small">Số điện thoại</label>
                                    <div class="invalid-feedback">{{ formErrors.phone }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select v-model="idAddressed.idProvince" class="form-select rounded-3 text-small"
                                        id="citySelect" :class="{ 'is-invalid': formErrors.province }">
                                        <option value="" disabled selected>Chọn Tỉnh/Thành</option>
                                        <option v-if="address.provinces" v-for="provin in address.provinces"
                                            :key="provin.id" :value="provin.id">
                                            {{ provin.name }}
                                        </option>
                                    </select>
                                    <label for="citySelect" class="small">Tỉnh / Thành phố</label>
                                    <div class="invalid-feedback">{{ formErrors.province }}</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select v-model="idAddressed.idDistrict" class="form-select rounded-3 text-small"
                                        id="districtSelect" :disabled="!idAddressed.idProvince"
                                        :class="{ 'is-invalid': formErrors.district }">
                                        <option value="" disabled selected>Chọn Quận/Huyện</option>
                                        <option v-if="address.districts" v-for="dist in address.districts"
                                            :key="dist.id" :value="dist.id">
                                            {{ dist.full_name }}
                                        </option>
                                    </select>
                                    <label for="districtSelect">Quận / Huyện</label>
                                    <div class="invalid-feedback">{{ formErrors.district }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select v-model="idAddressed.idWard" class="form-select rounded-3  text-small"
                                        id="wardSelect" :disabled="!idAddressed.idDistrict"
                                        :class="{ 'is-invalid': formErrors.ward }">
                                        <option value="" disabled selected>Chọn Phường/Xã</option>
                                        <option v-if="address.wards" v-for="ward in address.wards" :key="ward.id"
                                            :value="ward.id">
                                            {{ ward.full_name }}
                                        </option>
                                    </select>
                                    <label for="wardSelect">Phường / Xã</label>
                                    <div class="invalid-feedback small">{{ formErrors.ward }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-4">
                            <textarea v-model="form.street" class="form-control rounded-3 small" placeholder="Địa chỉ cụ thể"
                                id="floatingStreet" style="height: 100px" :class="{ 'is-invalid': formErrors.street }">
                        </textarea>
                            <label for="floatingStreet" class="small">Địa chỉ cụ thể (Số nhà, tên đường, tòa nhà...)</label>
                            <div class="invalid-feedback">{{ formErrors.street }}</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Loại địa chỉ</label>
                            <div class="d-flex gap-3 small">
                                <label class="address-type-radio px-3 py-2 rounded-3 border cursor-pointer"
                                    :class="{ 'active': form.type === 'home' }">
                                    <input type="radio" v-model="form.type" value="home" class="d-none">
                                    <i class="bi bi-house-door me-1"></i> Nhà riêng
                                </label>

                                <label class="address-type-radio px-3 py-2 rounded-3 border cursor-pointer"
                                    :class="{ 'active': form.type === 'office' }">
                                    <input type="radio" v-model="form.type" value="office" class="d-none">
                                    <i class="bi bi-briefcase me-1"></i> Văn phòng
                                </label>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input v-model="form.isDefault" class="form-check-input custom-switch" type="checkbox"
                                id="defaultSwitch">
                            <label class="form-check-label user-select-none small" for="defaultSwitch">
                                Đặt làm địa chỉ mặc định
                            </label>
                        </div>

                        <div class="d-flex gap-3 pt-2 border-top">
                            <button type="button" class="btn btn-light flex-grow-1 py-2 fw-bold text-muted rounded-3">
                                Trở về
                            </button>
                            <button type="submit"
                                class="btn btn-brand flex-grow-1 py-2 fw-bold text-white rounded-3 shadow-sm">
                                Thêm địa chỉ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>



<style scoped>
/* Màu chủ đạo */
:root {
    --brand-color: #3497e0;
}

.brand-color {
    color: #3497e0;
}

/* Custom Form Styles */
.form-control:focus,
.form-select:focus {
    border-color: #3497e0;
    box-shadow: 0 0 0 4px rgba(52, 151, 224, 0.15);
    /* Hiệu ứng glow đặc trưng */
}

/* Nút Submit */
.btn-brand {
    background-color: #3497e0;
    border: none;
    transition: 0.3s;
}

.btn-brand:hover {
    background-color: #287dbd;
    transform: translateY(-1px);
}

/* Radio Button "Loại địa chỉ" (Custom Style) */
.address-type-radio {
    border: 1px solid #dee2e6;
    color: #6c757d;
    transition: all 0.2s;
    background-color: #fff;
}

.address-type-radio:hover {
    background-color: #f8f9fa;
}

/* Trạng thái được chọn */
.address-type-radio.active {
    border-color: #3497e0;
    color: #3497e0;
    background-color: rgba(52, 151, 224, 0.05);
    font-weight: 600;
}

/* Switch Toggle Color */
.custom-switch:checked {
    background-color: #3497e0;
    border-color: #3497e0;
}

.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    color: #dc3545;
    font-size: 0.875rem;
}

select>option {
    font-size: 14px !important;
}

.text-small {
    font-size: 14px !important;
}
</style>
