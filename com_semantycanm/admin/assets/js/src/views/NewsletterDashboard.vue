<template>
  <div class="container mt-5">
    <div class="row">
      <div class="col-md-6 ">
        <div class="header-container">
          <h3>{{ stor.translations.AVAILABLE_LISTS }}</h3>
        </div>
        <div class="col-md-12 dragdrop-list">
          <ul class="list-group" id="availableListsUL">
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <h3>{{ stor.translations.SELECTED_LISTS }}</h3>
        <div class="col-md-12 dragdrop-list">
          <ul class="dropzone list-group" id="selectedLists"></ul>
        </div>
      </div>
    </div>

    <div class="row justify-content-center mt-5 submitarea">
      <div class="col-md-12">
        <?php if (!empty($response)): ?>
        <div class="alert alert-info">
          <?= htmlspecialchars($response, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php endif; ?>
        <h2 class="mb-4"><?php echo JText::_('SEND_NEWSLETTER'); ?></h2>
        <input type="hidden" id="currentNewsletterId" name="currentNewsletterId" value="">
        <input type="hidden" id="hiddenSelectedLists" name="selectedLists" value="">
        <div class="form-group">
          <label for="testEmails"><?php echo JText::_('TEST_ADDRESS'); ?></label>
          <input type="text" class="form-control" id="testEmails" name="testEmails">
        </div>
        <div class="form-group">
          <div class="input-group mb-3">
            <input type="text" class="form-control" id="subject" name="subject" required placeholder="Subject"
                   aria-label="Subject" aria-describedby="button-addon2">
            <button class="btn btn-outline-secondary" type="button" id="addSubjectBtn"
                    style="margin: 5px;"><?php echo JText::_('FETCH_SUBJECT'); ?>
            </button>
          </div>
        </div>
        <div class="form-group">
          <label for="messageContent"><?php echo JText::_('MESSAGE_CONTENT'); ?></label>
          <textarea class="form-control" id="messageContent" name="messageContent" rows="10" required
                    readonly></textarea>
        </div>
        <button @click="sendNewsletter">Send Newsletter</button>
        <button type="button" class="btn btn-secondary"
                id="saveNewsletterBtn"><?php echo JText::_('SAVE_NEWSLETTER'); ?></button>
        <button type="button" class="btn btn-secondary" id="toggleEditBtn"><?php echo JText::_('EDIT'); ?></button>

      </div>
    </div>
    <div class="row justify-content-center mt-5">
      <div class="col-md-12">
        <div class="header-container d-flex justify-content-between align-items-center">
          <h3 class="mb-4"><?php echo JText::_('NEWSLETTERS_LIST'); ?></h3>
          <div id="newsletterSpinner" class="spinner">
            <img
                src="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>administrator/components/com_semantycanm/assets/images/spinner.svg"
                alt="Loading" class="spinner-icon">
          </div>
          <div style="display: flex; justify-content: space-between; align-items: start;">
            <div style="color: gray; display: flex; gap: 5px; align-items: center;">
              <label for="totalNewsletterList">Total:</label><input type="text" id="totalNewsletterList"
                                                                    value="0" readonly
                                                                    style="width: 30px; border: none; background-color: transparent; color: inherit;"/>
              <label for="currentNewsletterList">Page:</label><input type="text" id="currentNewsletterList"
                                                                     value="1" readonly
                                                                     style="width: 20px; border: none; background-color: transparent; color: inherit;"/>
              <label for="maxNewsletterList">of</label><input type="text" id="maxNewsletterList" value="1"
                                                              readonly
                                                              style="width: 30px; border: none; background-color: transparent; color: inherit;"/>
            </div>
            <div class="pagination-container mb-3 me-2">
              <a class="btn btn-primary btn-sm" href="#"
                 id="firstPageNewsletterList"><?php echo JText::_('FIRST'); ?></a>
              <a class="btn btn-primary btn-sm" href="#"
                 id="previousPageNewsletterList"><?php echo JText::_('PREVIOUS'); ?></a>
              <a class="btn btn-primary btn-sm" href="#"
                 id="nextPageNewsletterList"><?php echo JText::_('NEXT'); ?></a>
              <a class="btn btn-primary btn-sm" href="#"
                 id="lastPageNewsletterList"><?php echo JText::_('LAST'); ?></a>
            </div>
          </div>

        </div>
        <div class="table-responsive" style="height: 200px;">
          <table class="table table-fixed">
            <thead>
            <?php
                    $refreshIconUrl = \Joomla\CMS\Uri\Uri::root() . "administrator/components/com_semantycanm/assets/images/refresh.png";
                    ?>

            <tr>
              <th class="col-1">
                <button class="btn btn-outline-secondary refresh-button" type="button"
                        id="refreshNewsLettersButton">
                  <img src="<?php echo $refreshIconUrl; ?>" alt="Refresh" class="refresh-icon">
                </button>
              </th>
              <th><?php echo JText::_('SUBJECT'); ?></th>
              <th><?php echo JText::_('REGISTERED'); ?></th>
            </tr>
            </thead>
            <tbody id="newsLettersList">
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import {defineComponent, onMounted, reactive} from 'vue';
import {useGlobalStore} from "../stores/globalStore";

export default defineComponent({
  name: 'NewsletterComponent',

  setup() {
    const store = useGlobalStore();
    const state = reactive({
      currentNewsletterId: '',
      testEmails: '',
      subject: '',
      messageContent: '',
      // ... other data properties ...
    });

    // Methods
    const sendNewsletter = async () => {
      // Implement your send newsletter logic here
      console.log('Sending newsletter...');
    };

    // onMounted lifecycle hook
    onMounted(() => {
      // Your on mounted logic here
      console.log('Component mounted');
      // Example: Load initial data, set up event listeners, etc.
    });


    return {
      store,
      state,
      sendNewsletter
    };
  },
});
</script>

<style scoped>
/* Your component-specific styles here */
</style>
