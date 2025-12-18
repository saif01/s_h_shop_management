<template>
    <div>
        <!-- dbFormatDate: {{ dbFormatDate }} -->
        <v-menu v-model="isMenuOpen" :close-on-content-click="false">
            <template v-slot:activator="{ props }">
                <v-text-field :label="dateFieldLavel" :placeholder="fieldPlaceHolder" :model-value="computedCustomValue"
                    readonly v-bind="props" clearable @click:clear="onClear()" density="compact"
                    :class="required ? 'required' : null" :variant="variant ? variant : 'outlined'"
                    :rules="[required ? (v => !!v || `This ${dateFieldLavel} is required`) : null]">
                </v-text-field>
            </template>
            <v-date-picker show-adjacent-months hide-header hide-actions position="sticky" v-model="selectedDate"
                @update:modelValue="isMenuOpen = false, initialDate = null"
                :min="min ? yesterdayIso : null"></v-date-picker>
        </v-menu>
    </div>
</template>

<script>
export default {

    props: ["fieldLabel", "required", "variant", "min", "initialDate"],

    data() {
        return {
            isMenuOpen: null,
            selectedDate: (this.initialDate == null) ? null : new Date(this.initialDate),
            dbFormatDate: null,
            dateFieldLavel: this.fieldLabel ?? "Selected date",
            fieldPlaceHolder: "Enter " + this.fieldLabel ?? "Date",
            yesterdayIso: new Date(new Date().setDate(new Date().getDate() - 1)),
        }
    },

    computed: {
        computedCustomValue() {
            if (this.selectedDate) {
                return this.frmtInsideDate(this.selectedDate)
            }
        },
    },

    watch: {
        computedCustomValue: function (val) {
            // console.log('computedCustomValue watch', val, this.dbFormatDate)
            this.$emit("trigerInputValue", this.dbFormatDate);
        },
        initialDate: function (newVal) {
            if (newVal) {
                this.selectedDate = new Date(newVal);
            } else {
                this.selectedDate = null;
            }
        },
    },



    methods: {

        // onClear
        onClear() {
            this.selectedDate = null;
            this.dbFormatDate = null;
        },

        // frmtInsideDate
        frmtInsideDate(date) {
            // Convert the input string to a Date object
            const inputDate = new Date(date)

            // Extract day, month, and year components
            const dday = inputDate.getDate()
            const mmonth = inputDate.getMonth() + 1 // Note: Months are zero-indexed, so we add 1
            const YYYY = inputDate.getFullYear()

            const DD = dday.toString().padStart(2, '0')
            const MM = mmonth.toString().padStart(2, '0')

            // DD/MM/YYYY
            const formattedDate = `${DD}/${MM}/${YYYY}`

            // YYYY-MM-DD
            this.dbFormatDate = `${YYYY}-${MM}-${DD}`

            //console.log(formattedDate) // Output: "28/12/2023"
            return formattedDate
        },
    },

    // created(){
    //     console.log('initialDate ', this.initialDate)
    // }
}
</script>
