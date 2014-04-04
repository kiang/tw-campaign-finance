package tw.g0v

import org.apache.pdfbox.exceptions._
import org.apache.pdfbox.pdmodel._
import org.apache.pdfbox.pdmodel.common.PDStream
import org.apache.pdfbox.util._

import scala.collection.JavaConverters._
import scala.io._
import java.io._

case class PDFFile(id: Int, file: String, page: Int)

object Extractor extends App {
  val lines = Source.fromFile("output.csv").getLines.toList
  val files = lines.map(line => {
    val Array(id, file, page) = line.split(",").take(3)
    
    PDFFile(id.toInt, "pdf/" + (if (file.startsWith("\"") && file.endsWith("\"")) file.drop(1).dropRight(1) else file), page.toInt)
    
  })

  files.foreach((f: PDFFile) => {
    println(s"processing: ${f.id}")
    val doc = PDDocument.load(f.file)
    val printer = new PrintTextLocations(new File(s"output/${f.id}.csv"))

    val pages: List[PDPage] = doc.getDocumentCatalog().getAllPages().asScala.toList.asInstanceOf[List[PDPage]]
    val page = pages(f.page)
    val contents = page.getContents
    if (contents != null) {
      printer.processStream(page, page.findResources, page.getContents.getStream)
    }
    printer.close
    doc.close
  })
}

class PrintTextLocations(val f: File) extends PDFTextStripper {
  val out = new PrintWriter(f, "UTF-8")

  def quoteIfNeeded(s: String): String = {
    val needToQuote = s.contains("\"") || s.contains(",")
    if (needToQuote) {
      "\"" + s.replace("\"", "\"\"") + "\""
    } else s
  }

  override def processTextPosition(text: TextPosition) =
  {
    out.println(quoteIfNeeded(text.getCharacter) + "," + 
                text.getXDirAdj + "," + 
                text.getYDirAdj + "," + 
                text.getFontSize + "," +
                text.getXScale + "," +
                text.getHeightDir + "," +
                text.getWidthOfSpace + "," +
                text.getWidthDirAdj)
  }

  def close = {
    out.close
  }
}