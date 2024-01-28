import {create} from 'naive-ui';

const bootstrapBlue = '#0d3c81'; // Replace with the correct Bootstrap blue if different

const smtcaTheme = create({
    themeOverrides: {
        common: {

            primaryColor: bootstrapBlue,
            primaryColorHover: bootstrapBlue, // Adjust the hover color if needed
            primaryColorPressed: bootstrapBlue, // Adjust the pressed color if needed
            primaryColorSuppl: bootstrapBlue, // Adjust the suppl color if needed
        },
        Pagination: {
            itemColorActive: bootstrapBlue, // Change active page number color
            itemBorderColorActive: bootstrapBlue, // Change active page number border color
            itemColorHover: bootstrapBlue, // Change hover color if needed
            // ...any other pagination-related colors you want to override
        },
        // ...other component overrides
    },
});

export default smtcaTheme;
