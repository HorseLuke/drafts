package testcase;

import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.util.zip.ZipEntry;
import java.util.zip.ZipException;
import java.util.zip.ZipFile;
import java.util.zip.ZipOutputStream;

public class Android_bug_8219321_duplicate_name_testcase {
	
	//http://www.h-online.com/open/news/item/Bluebox-s-Android-masterkey-hole-identified-1913097.html
	//https://jira.cyanogenmod.org/browse/CYAN-1602
	public static void main(String[] args) throws Exception {
        String entryName = "test_file_name1";
        String tmpName = "test_file_name2";

        // create the template data;
        ByteArrayOutputStream bytesOut = new ByteArrayOutputStream();
        ZipOutputStream out = new ZipOutputStream(bytesOut);
        ZipEntry ze1 = new ZipEntry(tmpName);
        out.putNextEntry(ze1);
        out.closeEntry();
        ZipEntry ze2 = new ZipEntry(entryName);
        out.putNextEntry(ze2);
        out.closeEntry();
        out.close();

        // replace the bytes we don't like
        byte[] buf = bytesOut.toByteArray();
        replaceBytes(tmpName.getBytes(), entryName.getBytes(), buf);

        // write the result to a file
        //请将“C:\\TEMP\\”改成其它存在的目录
        File badZip = File.createTempFile("badzip", "zip", new File("C:\\TEMP\\"));
        
        /*
         File badZip = File.createTempFile("badzip", "zip");
        */
        //badZip.deleteOnExit();
        FileOutputStream outstream = new FileOutputStream(badZip);
        outstream.write(buf);
        outstream.close();

        // see if we can still handle it
        try {
            ZipFile bad = new ZipFile(badZip);
            System.out.println("handled");
        } catch (ZipException expected) {
        }
	}
	
    private static void replaceBytes(byte[] original, byte[] replacement, byte[] buffer) throws Exception {
        // Gotcha here: original and replacement must be the same length
        assertEquals(original.length, replacement.length);
        boolean found;
        for(int i=0; i < buffer.length - original.length; i++) {
            found = false;
            if (buffer[i] == original[0]) {
                found = true;
                for (int j=0; j < original.length; j++) {
                    if (buffer[i+j] != original[j]) {
                        found = false;
                        break;
                    }
                }
            }
            if (found) {
                for (int j=0; j < original.length; j++) {
                    buffer[i+j] = replacement[j];
                }
            }
        }
    }
    
    private static void assertEquals(int a, int b) throws Exception {
    	if(a != b){
    		throw new Exception("assertEquals failed");
    	}
    }
	
	
}
