<script setup>
import { reactive, computed, watch, onMounted } from 'vue';
import api from '../../axios'; // Đảm bảo đường dẫn này đúng trong dự án của bạn

// 1. Props: Nhận thêm editData để phục vụ chức năng sửa
const props = defineProps({
    openModal: Boolean,
    mode: {
        type: String,
        default: 'create' // 'create' | 'update'
    },
    editData: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'save']);

const handleCloseModal = () => {
    emit('close');
};

// 2. State quản lý dữ liệu form (Text để gửi về Backend)
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

// 3. State quản lý ID cho Dropdown (Để UI hiển thị đúng)
const idAddressed = reactive({
    idProvince: '',
    idDistrict: '',
    idWard: '',
});

// 4. Danh sách địa chính
const address = reactive({
    provinces: [],
    districts: [],
    wards: []
});

const formErrors = reactive({});

// --- VALIDATION ---
const validateField = (field, value) => {
    switch (field) {
        case 'fullName': return value.trim() ? '' : 'Vui lòng nhập họ và tên';
        case 'phone':
            const phoneRegex = /^[0-9]{10,11}$/;
            if (!value.trim()) return 'Vui lòng nhập số điện thoại';
            if (!phoneRegex.test(value)) return 'Số điện thoại không hợp lệ';
            return '';
        case 'province': return value.trim() ? '' : 'Vui lòng chọn tỉnh/thành phố';
        case 'district': return value.trim() ? '' : 'Vui lòng chọn quận/huyện';
        case 'ward': return value.trim() ? '' : 'Vui lòng chọn phường/xã';
        case 'street': return value.trim() ? '' : 'Vui lòng nhập địa chỉ chi tiết';
        default: return '';
    }
};

const validateForm = () => {
    let isValid = true;
    // Map ID state to form text to ensure validation passes for selects
    if (!idAddressed.idProvince) form.province = '';
    if (!idAddressed.idDistrict) form.district = '';
    if (!idAddressed.idWard) form.ward = '';

    Object.keys(form).forEach((field) => {
        // Bỏ qua type và isDefault khi validate
        if (field !== 'type' && field !== 'isDefault') {
            const error = validateField(field, form[field]);
            formErrors[field] = error;
            if (error) isValid = false;
        }
    });
    return isValid;
};

// --- API CALLS ---
const fetchProinces = async () => {
    try {
        const res = await api.get('/address/provinces');
        address.provinces = res.data.data || res.data; // Tùy cấu trúc res trả về
    } catch (err) {
        console.log("Lỗi tải tỉnh thành: ", err);
    }
};

const fetchDistricts = async (provinceId) => {
    try {
        const res = await api.get(`/address/districts/${provinceId}`);
        address.districts = res.data.data || res.data;
    } catch (err) {
        console.log("Lỗi tải quận huyện: ", err);
    }
};

const fetchWards = async (districtId) => {
    try {
        const res = await api.get(`/address/wards/${districtId}`);
        address.wards = res.data.data || res.data;
    } catch (err) {
        console.log("Lỗi tải phường xã: ", err);
    }
};

// --- COMPUTED NAMES ---
const fullNameProvince = computed(() => {
    const province = address.provinces.find(pr => pr.id == idAddressed.idProvince);
    return province ? province.name : '';
});
const fullNameDistrict = computed(() => {
    const district = address.districts.find(dt => dt.id == idAddressed.idDistrict);
    // Lưu ý: API của bạn có thể trả về name hoặc full_name, hãy chọn đúng
    return district ? (district.full_name || district.name) : '';
});
const fullNameWard = computed(() => {
    const ward = address.wards.find(wd => wd.id == idAddressed.idWard);
    return ward ? (ward.full_name || ward.name) : '';
});

// --- WATCHERS (Xử lý khi người dùng chọn tay) ---
watch(() => idAddressed.idProvince, (newProvince) => {
    if (newProvince) {
        // Nếu thay đổi tỉnh -> Load huyện mới, update Text form, Reset huyện/xã cũ
        if (form.province !== fullNameProvince.value) { // Chỉ reset nếu thực sự thay đổi (tránh xung đột logic update)
            idAddressed.idDistrict = '';
            idAddressed.idWard = '';
            address.districts = [];
            address.wards = [];
            fetchDistricts(newProvince);
            form.province = fullNameProvince.value;
        }
    }
});

watch(() => idAddressed.idDistrict, (newDistrict) => {
    if (newDistrict) {
        if (form.district !== fullNameDistrict.value) {
            idAddressed.idWard = '';
            address.wards = [];
            fetchWards(newDistrict);
            form.district = fullNameDistrict.value;
        }
    }
});

watch(() => idAddressed.idWard, (newWard) => {
    if (newWard) {
        form.ward = fullNameWard.value;
    }
});

// --- LOGIC MAP NGƯỢC (TÊN -> ID) CHO CHỨC NĂNG SỬA ---
const isMatch = (str1, str2) => {
    return str1?.toLowerCase().trim() === str2?.toLowerCase().trim();
};

const fillDataForUpdate = async () => {
    if (!props.editData) return;

    // 1. Fill Text Data
    form.fullName = props.editData.recipient_name;
    form.phone = props.editData.recipient_phone;
    form.street = props.editData.street_address;
    form.type = props.editData.type || 'home';
    form.isDefault = !!props.editData.is_default; // Ép kiểu boolean

    // 2. Map Address (Tuần tự: Tỉnh -> Huyện -> Xã)

    // Đảm bảo list tỉnh đã có
    if (address.provinces.length === 0) await fetchProinces();

    // Tìm Tỉnh
    const foundProvince = address.provinces.find(p => isMatch(p.name, props.editData.province));

    if (foundProvince) {
        idAddressed.idProvince = foundProvince.id;
        form.province = foundProvince.name;

        // Gọi API Huyện và chờ
        await fetchDistricts(foundProvince.id);

        // Tìm Huyện (Check cả name và full_name cho chắc)
        const foundDistrict = address.districts.find(d =>
            isMatch(d.name, props.editData.district) || isMatch(d.full_name, props.editData.district)
        );

        if (foundDistrict) {
            idAddressed.idDistrict = foundDistrict.id;
            form.district = foundDistrict.full_name || foundDistrict.name;

            // Gọi API Xã và chờ
            await fetchWards(foundDistrict.id);

            // Tìm Xã
            const foundWard = address.wards.find(w =>
                isMatch(w.name, props.editData.ward) || isMatch(w.full_name, props.editData.ward)
            );

            if (foundWard) {
                idAddressed.idWard = foundWard.id;
                form.ward = foundWard.full_name || foundWard.name;
            }
        }
    }
};

const resetFormState = () => {
    // Reset Form Text
    Object.assign(form, {
        fullName: '', phone: '', province: '', district: '', ward: '', street: '',
        type: 'home', isDefault: false
    });
    // Reset IDs
    idAddressed.idProvince = '';
    idAddressed.idDistrict = '';
    idAddressed.idWard = '';
    // Reset Errors
    Object.keys(formErrors).forEach(key => formErrors[key] = '');
};

// Khi mở Modal
watch(() => props.openModal, async (isOpen) => {
    if (isOpen) {
        if (props.mode === 'update') {
            await fillDataForUpdate();
        } else {
            resetFormState();
        }
    }
});

onMounted(() => {
    fetchProinces();
});

// --- SUBMIT HANDLER ---
const handleSubmit = async () => {
    if (!validateForm()) {
        console.log("Form validation failed:", formErrors);
        return;
    }

    try {
        let res;
        if (props.mode === 'create') {
            // API Create
            res = await api.post('/addresses', form);
        } else {
            // API Update - Cần ID của bản ghi Address (props.editData.id)
            res = await api.put(`/addresses/${props.editData.address_id}`, form);
        }

        // Kiểm tra status code (201 cho create, 200 cho update)
        if (res.status === 201 || res.status === 200) {
            const msg = props.mode === 'create' ? 'Thêm mới' : 'Cập nhật';
            // alert(`${msg} địa chỉ thành công`);
            emit('save');
            handleCloseModal();
        }
    } catch (err) {
        console.log('Lỗi khi gọi API: ', err);
        alert('Có lỗi xảy ra, vui lòng thử lại.');
    }
};
</script>

<template>
    <div v-if="props.openModal" class="fade show modal" tabindex="-1"
        style="display: block; background-color: rgba(0,0,0,0.5)" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient border-0">
                    <div>
                        <div class="modal-title fw-bold color-main mb-0 fs-5">
                            {{ props.mode === 'create' ? 'Thêm địa chỉ mới' : 'Cập nhật địa chỉ' }}
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
                                    <label for="floatingName" class="small">Họ và tên người nhận</label>
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
                                        <option v-for="provin in address.provinces" :key="provin.id" :value="provin.id">
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
                                        <option v-for="dist in address.districts" :key="dist.id" :value="dist.id">
                                            {{ dist.full_name || dist.name }}
                                        </option>
                                    </select>
                                    <label for="districtSelect">Quận / Huyện</label>
                                    <div class="invalid-feedback">{{ formErrors.district }}</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select v-model="idAddressed.idWard" class="form-select rounded-3 text-small"
                                        id="wardSelect" :disabled="!idAddressed.idDistrict"
                                        :class="{ 'is-invalid': formErrors.ward }">
                                        <option value="" disabled selected>Chọn Phường/Xã</option>
                                        <option v-for="ward in address.wards" :key="ward.id" :value="ward.id">
                                            {{ ward.full_name || ward.name }}
                                        </option>
                                    </select>
                                    <label for="wardSelect">Phường / Xã</label>
                                    <div class="invalid-feedback small">{{ formErrors.ward }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-4">
                            <textarea v-model="form.street" class="form-control rounded-3 small"
                                placeholder="Địa chỉ cụ thể" id="floatingStreet" style="height: 100px"
                                :class="{ 'is-invalid': formErrors.street }">
                            </textarea>
                            <label for="floatingStreet" class="small">Địa chỉ cụ thể (Số nhà, tên đường...)</label>
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
                            <button type="button" class="btn btn-light flex-grow-1 py-2 fw-bold text-muted rounded-3"
                                @click="handleCloseModal">
                                Trở về
                            </button>
                            <button type="submit"
                                class="btn btn-brand flex-grow-1 py-2 fw-bold text-white rounded-3 shadow-sm">
                                {{ props.mode === 'create' ? 'Thêm địa chỉ' : 'Lưu thay đổi' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
:root {
    --brand-color: #3497e0;
}

.brand-color {
    color: #3497e0;
}

.form-control:focus,
.form-select:focus {
    border-color: #3497e0;
    box-shadow: 0 0 0 4px rgba(52, 151, 224, 0.15);
}

.btn-brand {
    background-color: #3497e0;
    border: none;
    transition: 0.3s;
}

.btn-brand:hover {
    background-color: #287dbd;
    transform: translateY(-1px);
}

.address-type-radio {
    border: 1px solid #dee2e6;
    color: #6c757d;
    transition: all 0.2s;
    background-color: #fff;
    cursor: pointer;
}

.address-type-radio:hover {
    background-color: #f8f9fa;
}

.address-type-radio.active {
    border-color: #3497e0;
    color: #3497e0;
    background-color: rgba(52, 151, 224, 0.05);
    font-weight: 600;
}

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
