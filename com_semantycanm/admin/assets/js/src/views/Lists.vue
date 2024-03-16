<template>
  <n-divider title-placement="left">User Groups</n-divider>
  <n-form inline ref="formRef" :rules="rules" :model="formValue">
    <div class="container mt-3">
      <div class="row">
        <div class="col">
          <!--          <h3>{{ globalStore.translations.AVAILABLE_USER_GROUPS }}</h3>-->
          <draggable v-model="formValue.availableGroups" class="list-group" group="shared" itemKey="id">
            <template #item="{ element }">
              <div class="list-group-item" :key="element.id">
                {{ element.title }}
              </div>
            </template>
          </draggable>
        </div>
        <div class="col">
          <!--          <h3>{{ globalStore.translations.SELECTED_USER_GROUPS }}</h3>-->
          <draggable v-model="formValue.selectedGroups" class="list-group" group="shared" itemKey="id">
            <template #item="{ element }">
              <div class="list-group-item" :key="element.id">
                {{ element.title }}
              </div>
            </template>
          </draggable>
        </div>
      </div>
      <div class="row mt-3">
        <div class="col-3 d-flex align-items-center">
          <n-form-item label="Mailing List Name" path="groupName" class="w-100">
            <n-input v-model:value="formValue.groupName"
                     size="large"
                     style="width: 100%"
                     id="mailingListName"
                     type="text"
                     placeholder="Mailing List Name"/>
          </n-form-item>
        </div>
        <div class="col  d-flex align-items-center">
          <n-button-group>
            <n-button id="saveGroup"
                      type="primary"
                      size="large"
                      @click="validateAndSave">{{ globalStore.translations.SAVE }}
            </n-button>
            <n-button id="cancelEditing"
                      type="success"
                      size="large"
                      @click="cancelList">{{ globalStore.translations.CANCEL }}
            </n-button>
          </n-button-group>
        </div>
      </div>
      <n-divider title-placement="left">Available Mailing Lists</n-divider>
      <!--      <div class="row mt-3">
              <div class="col">
                <h3>{{ globalStore.translations.MAILING_LISTS }}</h3>
              </div>
            </div>-->
      <div class="row">
        <div class="col">
          <n-data-table
              remote
              size="large"
              :columns="columns"
              :data="mailingListStore.docsListPage.docs"
              :bordered="false"
              :pagination="pagination"
              @update:page="handlePageChange"
              @update:page-size="handlePageSizeChange"
          />
          <input type="hidden" id="mailingListMode" value=""/>
        </div>
      </div>
    </div>
  </n-form>

</template>

<script>
import {h, onMounted, reactive, ref} from 'vue';
import {
  NButton,
  NButtonGroup,
  NDataTable,
  NDivider,
  NForm,
  NFormItem,
  NFormItemGi,
  NGrid,
  NGridItem,
  NInput,
  NSpace,
  useMessage
} from "naive-ui";
import {useUserGroupStore} from "../stores/userGroupStore";
import {useMailingListStore} from "../stores/mailinglistStore";
import {useGlobalStore} from "../stores/globalStore";
import draggable from 'vuedraggable';

export default {
  name: 'Lists',
  components: {
    NGrid,
    NGridItem,
    NButton,
    NButtonGroup,
    NSpace,
    NInput,
    NDataTable,
    NForm,
    NFormItem,
    NFormItemGi,
    NDivider,
    draggable
  },
  setup() {
    const formRef = ref(null);
    const globalStore = useGlobalStore();
    const userGroupStore = useUserGroupStore();
    const mailingListStore = useMailingListStore();
    const message = useMessage();

    const state = reactive({
      mailingListMode: ''
    });

    const pagination = reactive({
      page: 1,
      pageSize: 5,
      pageCount: 1,
      itemCount: 0,
      size: 'large'
    });

    function handlePageChange(page) {
      mailingListStore.fetchMailingList(page, pagination.pageSize, pagination);
    }

    function handlePageSizeChange(pageSize) {
      mailingListStore.fetchMailingList(pagination.page, pageSize, pagination);
    }

    onMounted(async () => {
      await userGroupStore.getList();
      await mailingListStore.fetchMailingList(1, 5, pagination);
      formValue.value.availableGroups = userGroupStore.documentsPage.docs;
    });

    const validateAndSave = (e) => {
      e.preventDefault();

      const selectedGroupsValidation = rules.selectedGroups.validator(null, formValue.value.selectedGroups);
      if (!selectedGroupsValidation) {
        message.error(rules.selectedGroups.message);
        return;
      }

      formRef.value.validate((errors) => {
        if (!errors) {
          userGroupStore.saveList(formRef.value.model, '').then(() => {
            state.mailingListMode = '';
            formValue.value.groupName = '';
            formValue.value.selectedGroups = [];
          }).catch((error) => {
            console.error("Error saving the list:", error);
            message.error("Failed to save the list.");
          });
        } else {
          Object.keys(errors).forEach(fieldName => {
            const fieldError = errors[fieldName];
            if (fieldError && fieldError.length > 0) {
              message.error(fieldError[0].message);
            }
          });
        }
      });
    };

    const cancelList = () => {
      formValue.value.groupName = '';
      formValue.value.selectedGroups = [];
      formValue.value.availableGroups = userGroupStore.documentsPage.docs;
      state.mailingListMode = '';
    };

    const rules = {
      groupName: {
        required: true,
        message: 'Mailing list name cannot be empty'
      },
      selectedGroups: {
        validator(rule, value) {
          return value && value.length > 0;
        },
        message: 'At least one group should be selected'
      }
    };

    const formValue = ref({
      groupName: '',
      availableGroups: [],
      selectedGroups: []
    });

    const createColumns = () => {
      return [
        {
          title: 'Name',
          key: 'name'
        },
        {
          title: 'Created',
          key: 'reg_date'
        },
        {
          title: "Action",
          key: "actions",
          render(row) {
            return [
              h(NButton, {
                onClick: () => { /* handle edit */
                },
                style: 'margin-right: 8px;',
                strong: true,
                secondary: true,
                type: "primary",
                size: "small",
              }, {default: () => 'Edit'}),
              h(NButton, {
                onClick: () => { /* handle delete */
                },
                strong: true,
                secondary: true,
                type: "error",
                size: "small",
              }, {default: () => 'Delete'})
            ];
          }
        }
      ];
    };


    return {
      state,
      globalStore,
      userGroupStore,
      mailingListStore,
      cancelList,
      validateAndSave,
      rules,
      columns: createColumns(),
      pagination,
      formValue,
      formRef,
      message,
      handlePageSizeChange,
      handlePageChange
    };
  }
};
</script>
