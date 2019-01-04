import java.io.BufferedInputStream;
import java.io.BufferedReader;
import java.io.File;
import java.io.FileOutputStream;
import java.io.FileReader;
import java.io.PrintWriter;
import java.net.URL;
import java.nio.channels.*;
import java.sql.Date;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.TimeUnit;

public class CheatersUpdate {
	public static void main(String[] args) {
	    final ScheduledExecutorService executorService = Executors.newSingleThreadScheduledExecutor();
	    executorService.scheduleAtFixedRate(new Runnable() {
	        @Override
	        public void run() {
	            myTask();
	        }
	    }, 0, 15, TimeUnit.MINUTES);
	}

	private static void myTask() {
		try {
			File file = new File("/var/www/html/assets/cache/lastupdated.txt");
			BufferedReader br = new BufferedReader(new FileReader(file));
			String s;
			String lastupdated = "";
			while ((s = br.readLine()) != null) {
				lastupdated = s;
			}
			long lastupdatedLong = Long.parseLong(lastupdated);
			if((System.currentTimeMillis() - lastupdatedLong) >= 3600000) {
				System.out.println("Updating...");
				System.out.println("At time: " + System.currentTimeMillis());
				update();
				updatePlayers();
			} else {
				System.out.println("Not updating");
			}
			br.close();
		} catch(Exception e) {
			System.out.println("Error: " + e);
		}
	}

	public static void update() {
		try {
			PrintWriter writer = new PrintWriter("/var/www/html/assets/cache/lastupdated.txt", "UTF-8");
			long currentMillis = System.currentTimeMillis();
			writer.print(currentMillis);
			writer.close();
			Date currentDate = new Date(currentMillis);
			DateFormat df = new SimpleDateFormat("HH:mm dd/MM/yyyy");
			String outputDate = "Last updated at " + df.format(currentDate);
			PrintWriter writerNew = new PrintWriter("/var/www/html/assets/cache/lastupdatedDate.txt", "UTF-8");
			writerNew.print(outputDate);
			writerNew.close();
			System.out.println(outputDate);
		} catch(Exception e) {
			System.out.println("Update error: " + e);
		}
	}

	public static void updatePlayers() {
		try {
			File file = new File("/var/www/html/assets/cache/players.txt");
			BufferedReader br = new BufferedReader(new FileReader(file));
			String s;
			String player = "";
			int counter = 0;
			while ((s = br.readLine()) != null) {
				counter++;
				player = s;
				String playerSplit[] = player.split(" ");
				String SteamID = playerSplit[3];
				String SteamAPI = "2126B1B921533D924200B21ADD48693F";
				if(Long.parseLong(SteamID) > 1) {
					String globalFile = "/var/www/html/assets/cache/players/";
					String urlBanned = "http://api.steampowered.com/ISteamUser/GetPlayerBans/v1/?key=" + SteamAPI + "&steamids=" + SteamID;
					String fileLocation = globalFile + SteamID + "Banned.json";
					updatePlayerURL(urlBanned, SteamID, fileLocation);
					String urlProfile = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=" + SteamAPI + "&steamids=" + SteamID;
					fileLocation = globalFile + SteamID + ".json";
					updatePlayerURL(urlProfile, SteamID, fileLocation);
					String urlCSGO = "https://api.steampowered.com/ISteamUserStats/GetUserStatsForGame/v0002/?appid=730&key=" + SteamAPI + "&steamid=" + SteamID;
					fileLocation = globalFile + SteamID + "CSGO.json";
					updatePlayerURL(urlCSGO, SteamID,fileLocation);
				}
				System.out.println("Done keyid " + counter);
			}
			System.out.println("\nFinished");
			br.close();
		} catch(Exception e) {
			System.out.println("Update error: " + e);
		}
	}

	public static void updatePlayerURL(String URL, String SteamID, String fileLocation) {
		try (BufferedInputStream inputStream = new BufferedInputStream(new URL(URL).openStream());
										FileOutputStream fileOS = new FileOutputStream(fileLocation)) {
		    byte data[] = new byte[1024];
		    int byteContent;
		    while ((byteContent = inputStream.read(data, 0, 1024)) != -1) {
		        fileOS.write(data, 0, byteContent);
		    }
		} catch (Exception e) {
			System.out.println(SteamID + " CSGO Doesn't exist");
		}
	}
}
