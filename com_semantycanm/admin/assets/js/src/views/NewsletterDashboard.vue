<template>
  <n-form inline ref="newsletterFormRef" :rules="newsLetterRules" :model="newsLetterFormValue">
    <div class="container mt-3">

      <div class="row">
        <div class="col">
          <h3>{{ globalStore.translations.AVAILABLE_LISTS }}</h3>
          <div class="col-md-12 dragdrop-list">
            <ul ref="availableListsUlRef" class="list-group">
              <li v-for="ml in mailingListStore.docsListPage.docs" :key="ml.id" class="list-group-item"
                  :id="ml.id">{{ ml.name }}
              </li>
            </ul>
          </div>
        </div>
        <div class="col">
          <h3>{{ globalStore.translations.SELECTED_LISTS }}</h3>
          <div class="col-md-12 dragdrop-list">
            <ul ref="selectedListsUlRef" class="dropzone list-group"></ul>
          </div>
        </div>
      </div>

      <div class="row justify-content-center mt-3">
        <div class="col">
          <h3>{{ globalStore.translations.SEND_NEWSLETTER }}</h3>
          <input type="hidden" id="currentNewsletterId" name="currentNewsletterId" value="">
          <input type="hidden" id="hiddenSelectedLists" name="selectedLists" value="">
          <n-form-item :label=globalStore.translations.TEST_ADDRESS path="testEmail">
            <n-input v-model:value="newsLetterFormValue.testEmail"
                     placeholder="E-mail address"
                     size="large"
                     type="text"
                     id="testEmails"
                     name="testEmails"/>
          </n-form-item>
        </div>
      </div>

      <div class="row justify-content-center">
        <div class="col">
          <n-form-item :label="globalStore.translations.SUBJECT" path="subject">
            <n-input-group>
              <n-input v-model:value="newsLetterFormValue.subject"
                       size="large"
                       type="text"
                       id="subject"
                       placeholder="Subject"
                       style="flex-grow: 1;"/>
              <n-button size="large"
                        type="tertiary"
                        @click="setSubject">{{ globalStore.translations.FETCH_SUBJECT }}
              </n-button>
            </n-input-group>
          </n-form-item>
        </div>
      </div>

      <div class="row">
        <div class="col">
          <div class="form-group">
            <n-form-item :label=globalStore.translations.MESSAGE_CONTENT path="messageContent">
              <n-input
                  v-model:value="newsLetterFormValue.messageContent"
                  type="textarea"
                  rows="10"
                  placeholder=""
              />
            </n-form-item>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-4 d-flex align-items-center">
          <n-button-group>
            <n-button type="success"
                      size="large"
                      @click="sendNewsletter(false)">{{ globalStore.translations.SEND_NEWSLETTER }}
            </n-button>
            <n-button id="saveNewsletterBtn"
                      size="large"
                      type="primary"
                      @click="sendNewsletter(true)">{{ globalStore.translations.SAVE_NEWSLETTER }}
            </n-button>
            <n-button id="toggleEditBtn"
                      size="large"
                      type="primary"
                      @click="editContent">{{ globalStore.translations.EDIT }}
            </n-button>

          </n-button-group>
        </div>
        <div class="col-1 d-flex align-items-center">
          <n-switch :round="false" :rail-style="railStyle">
            <template #checked>
              On
            </template>
            <template #unchecked>
              Off
            </template>
          </n-switch>
        </div>
        <div class="col d-flex align-items-center me-5">
          <n-progress
              type="line"
              :percentage="100"
              :indicator-placement="'inside'"
          />
        </div>
      </div>

      <div class="row mt-4">
        <div class="col">
          <h3>{{ globalStore.translations.NEWSLETTERS_LIST }}</h3>
        </div>
      </div>

      <div class="row">
        <div class="col">
          <n-data-table
              remote
              size="large"
              :columns="columns"
              :data="newsLetterStore.docsListPage.docs"
              :bordered="false"
              :pagination="pagination"
              @update:page="handlePageChange"
              @update:page-size="handlePageSizeChange"
          />
        </div>
      </div>
    </div>
  </n-form>
</template>

<script>
import {defineComponent, h, nextTick, onMounted, reactive, ref} from 'vue';
import {useGlobalStore} from "../stores/globalStore";
import {
  NButton,
  NButtonGroup,
  NDataTable,
  NForm,
  NFormItem,
  NInput,
  NInputGroup,
  NProgress,
  NSpace,
  NSwitch,
  useMessage
} from "naive-ui";
import {useNewsletterStore} from "../stores/newsletterStore";
import {useMailingListStore} from "../stores/mailinglistStore";

export default defineComponent({
  name: 'NewsletterComponent',
  components: {
    NInput,
    NButton,
    NButtonGroup,
    NSpace,
    NSwitch,
    NDataTable,
    NInputGroup,
    NFormItem,
    NForm,
    NProgress
  },

  setup() {
    const newsletterFormRef = ref(null);
    const availableListsUlRef = ref(null);
    const selectedListsUlRef = ref(null);
    const globalStore = useGlobalStore();
    const mailingListStore = useMailingListStore();
    const newsLetterStore = useNewsletterStore();
    const message = useMessage();
    const state = reactive({
      currentNewsletterId: '',
      isTest: false
    });

    const newsLetterFormValue = ref({
      testEmail: '',
      subject: '',
      messageContent: '',
      selectedGroups: []
    });

    const pagination = reactive({
      page: 1,
      pageSize: 10,
      pageCount: 1,
      itemCount: 0,
      size: 'large'
    });

    function handlePageChange(page) {
      newsLetterStore.fetchNewsLetter(page, pagination.pageSize, pagination);
    }

    function handlePageSizeChange(pageSize) {
      newsLetterStore.fetchNewsLetter(pagination.page, pageSize, pagination);
    }

    onMounted(() => {
      mailingListStore.fetchMailingList(1, 100, undefined);
      newsLetterStore.fetchNewsLetter(1, 10, pagination);
      nextTick(() => {
        applyAndDropSet([availableListsUlRef.value, selectedListsUlRef.value]);
      });
    });
    const setSubject = () => {
      fetch('index.php?option=com_semantycanm&task=service.getSubject&type=random')
          .then(response => response.json())
          .then(data => {
            newsLetterFormValue.value.subject = data.data;
          })
          .catch(error => {
            console.log(error);
            showAlertBar("Error: " + error);
          });
    }

    const sendNewsletter = async (onlySave) => {
      newsletterFormRef.value.validate((errors) => {
        if (!errors) {
          const subj = newsLetterFormValue.value.subject;
          const msgContent = newsLetterFormValue.value.messageContent;
          const newsletterRequest = new NewsletterRequest();
          if (onlySave) {
            newsletterRequest.addNewsletter(subj, msgContent)
                .then(() => {
                  newsLetterStore.fetchNewsletters(1);
                })
          } else {
            let listItems;
            if (newsLetterFormValue.value.testEmail !== "") {
              listItems = [newsLetterFormValue.value.testEmail];
            } else {
              listItems = Array.from(document.querySelectorAll('#selectedLists li'))
                  .map(li => li.textContent.trim());
              if (listItems.length === 0) {
                message.warning("The list is empty");
                return;
              }
            }
            newsletterRequest.sendEmail(subj, msgContent, listItems)
                .then(() => {
                  newsLetterStore.fetchNewsletters(1);
                })
          }
        } else {
          Object.keys(errors).forEach(fieldName => {
            const fieldError = errors[fieldName];
            if (fieldError && fieldError.length > 0) {
              message.error(fieldError[0].message);
            }
          });
        }
      });
    }

    const editContent = () => {
      const messageContent = document.getElementById('messageContent');
      const toggleBtn = document.getElementById('toggleEditBtn');
      if (messageContent.hasAttribute('readonly')) {
        messageContent.removeAttribute('readonly');
        toggleBtn.textContent = 'Read-Only';
      } else {
        messageContent.setAttribute('readonly', 'readonly');
        toggleBtn.textContent = 'Edit';
      }
    };

    const applyAndDropSet = (lists) => {
      lists.forEach(list => {
        Sortable.create(list, {
          group: {
            name: 'shared',
            pull: true,
            put: true
          },
          animation: 150,
          sort: false
        });
      });
    };

    const newsLetterRules = {
      testEmail: {
        validator(rule, value) {
          if (!value) {
            return true;
          }
          const emailPattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
          return emailPattern.test(value);
        },
        message: 'Please enter a valid email address',
      },
      subject: {
        required: true,
        message: 'The subject cannot be empty'
      },
      messageContent: {
        required: true,
        message: 'Message content is empty. It cannot be saved'
      },
      selectedLists: {
        validator(rule, value) {
          return value && value.length > 0;
        },
        message: 'At least one group should be selected'
      }
    };

    const createColumns = () => {
      return [
        {
          title: 'Subject',
          key: 'subject'
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
      ]
    }

    return {
      globalStore,
      mailingListStore,
      newsLetterStore,
      state,
      newsletterFormRef,
      newsLetterRules,
      newsLetterFormValue,
      sendNewsletter,
      editContent,
      setSubject,
      availableListsUlRef,
      selectedListsUlRef,
      columns: createColumns(),
      pagination,
      railStyle: ({
                    focused,
                    checked
                  }) => {
        const style = {};
        if (checked) {
          style.background = "#06792a";
          if (focused) {
            style.boxShadow = "0 0 0 2px #d0305040";
          }
        }
        style.marginRight = "20px";
        return style;
      },
      handlePageSizeChange,
      handlePageChange
    };
  },
});
</script>

<style>
/*.send-actions-container button.btn {
  margin-right: 5px;
}*/

</style>
