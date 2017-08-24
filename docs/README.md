# Stockbase Integration Documentation




## How to build

1. Download and install the [R language tools](https://www.r-project.org/).
2. Download and install **XeLaTeX**:
   - On Mac OS: [with the BasicTeX package](http://www.texts.io/support/0001/).
   - On Windows: [with the MiKTeX installer](http://www.texts.io/support/0002/).
   - On Linux: with the `texlive-xetex` package (`texlive-collection-xetex` in some distros).
3. Download and install [RStudio](https://www.rstudio.com/products/rstudio/download/).
4. Run the RStudio. In the "Console" window execute following commands:
   ```
   install.packages("devtools")
   devtools::install_github("rstudio/bookdown")
   ```
5. Open the `stockbase-integration.Rproj` file as a project within RStudio.
6. Click on the **Build Book** button in the "Build" tab (this action as also available from the menu **Build** -> **Build All**).
   You can also build a single page preview, you can click on the **Knit** button on top of the file editor.


## Useful documentation

- [Authoring Books and Technical Documents with R Markdown](https://bookdown.org/yihui/bookdown/)
- [Pandoc's Markdown documentation](http://pandoc.org/MANUAL.html#pandocs-markdown)
- [knitr documentation](https://yihui.name/knitr/options/)
