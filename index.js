panel.plugin('hashsandsalt/yasumi', {
  sections: {
    holidays: {
      data: function () {
        return {
          label: null,
          year: null,
          holidays: []
        };
      },
      created: async function () {
        const response = await this.load();

        this.label = response.label;
        this.year = response.year;
        this.holidays = response.holidays;
      },
      template: `
      <k-section :label="label + ' ' + year">
        <k-collection :items="holidays" />
      </k-section>
    `
    }
  }
});
